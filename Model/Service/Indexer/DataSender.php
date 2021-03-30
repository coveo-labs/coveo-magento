<?php declare(strict_types=1);
namespace Coveo\Search\Model\Service\Indexer;


define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'SDK'. DIRECTORY_SEPARATOR .'SDKPushPHP'. DIRECTORY_SEPARATOR .'Enum.php');
require_once(__ROOT__. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'SDK'. DIRECTORY_SEPARATOR .'SDKPushPHP'. DIRECTORY_SEPARATOR .'CoveoConstants.php');
require_once(__ROOT__. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'SDK'. DIRECTORY_SEPARATOR .'SDKPushPHP'. DIRECTORY_SEPARATOR .'CoveoDocument.php');
require_once(__ROOT__. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'SDK'. DIRECTORY_SEPARATOR .'SDKPushPHP'. DIRECTORY_SEPARATOR .'CoveoPermissions.php');
require_once(__ROOT__. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'SDK'. DIRECTORY_SEPARATOR .'SDKPushPHP'. DIRECTORY_SEPARATOR .'CoveoPush.php');

use Coveo\Search\Api\Service\Config\IndexerConfigInterface;
use Coveo\Search\Api\Service\ConfigInterface;
use Coveo\Search\Api\Service\Indexer\DataSenderInterface;
use Coveo\Search\Api\Service\LoggerInterface;
use Coveo\Search\Model\Service\Indexer\Db\AttributesValuesIndexFlat;
use Coveo\Search\Model\Service\Indexer\Db\CatalogIndexFlat;
use Coveo\Search\Model\Service\Indexer\Db\StockIndexFlat;
use Coveo\Search\SDK\ClientBuilder;
use Coveo\Search\SDK\SDKPushPHP\Push as Push;
use Coveo\Search\SDK\SDKPushPHP\Document as Document;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class DataSender implements DataSenderInterface
{
    const CSV_SEPARATOR = ',';

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var IndexerConfigInterface
     */
    protected $indexerConfig;

    /**
     * @var ClientBuilder
     */
    protected $clientBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CatalogIndexFlat
     */
    protected $catalogIndexFlat;

    /**
     * @var StockIndexFlat
     */
    protected $stockIndexFlat;

    /**
     * @var AttributesValuesIndexFlat
     */
    protected $attributesValuesIndexFlat;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param ConfigInterface $config
     * @param IndexerConfigInterface $indexerConfig
     * @param LoggerInterface $logger
     * @param ClientBuilder $clientBuilder
     * @param CatalogIndexFlat $catalogIndexFlat
     * @param StockIndexFlat $stockIndexFlat
     * @param AttributesValuesIndexFlat $attributesValuesIndexFlat
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     */
    public function __construct(
        ConfigInterface $config,
        IndexerConfigInterface $indexerConfig,
        LoggerInterface $logger,
        ClientBuilder $clientBuilder,
        CatalogIndexFlat $catalogIndexFlat,
        StockIndexFlat $stockIndexFlat,
        AttributesValuesIndexFlat $attributesValuesIndexFlat,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem
    )
    {
        $this->config = $config;
        $this->indexerConfig = $indexerConfig;
        $this->logger = $logger;
        $this->clientBuilder = $clientBuilder;
        $this->catalogIndexFlat = $catalogIndexFlat;
        $this->stockIndexFlat = $stockIndexFlat;
        $this->attributesValuesIndexFlat = $attributesValuesIndexFlat;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
    }

    /**
     * @inheritdoc
     */
    public function sendCatalog()
    {
        $storeIds = $this->indexerConfig->getStores();

        $this->logger->info('[catalog export] Start exporting data for stores ' . implode(',', $storeIds) . '..');

        $headers = $this->indexerConfig->getAttributes();
        array_unshift($headers, 'id');
        array_unshift($headers, 'store_id');
        array_unshift($headers, 'documentId');
        sort($headers);

        foreach ($storeIds as $storeId) {
            $this->storeManager->setCurrentStore($storeId);

            $this->logger->debug("[catalog export] Start exporting catalog data for store $storeId");

            // Export catalog


            $catalogData = $this->catalogIndexFlat->extractData($storeId, $headers);
            if ($catalogData === null) {
                $this->logger->warn("[catalog export] An error occurred during store $storeId catalog data extract, skipping..");
                continue;
            }
            //$catalogCsvContent = $this->arrayToCsv($catalogData);

            // Export attributes --> Not needed

            /*$attributesData = $this->attributesValuesIndexFlat->extractData($storeId);
            if ($attributesData === null) {
                $this->logger->warn("[catalog export] An error occurred during store $storeId attributes data extract, skipping..");
                continue;
            }
            $attributeCsvContent = $this->arrayToCsv($attributesData);*/

            if ($this->indexerConfig->isDryRunModeEnabled()) {
              $catalogJsonContent = $this->arrayToJson($catalogData);
              $this->writeCsvToFile($catalogJsonContent, $storeId, 'catalog_');
                //$this->writeCsvToFile($attributeCsvContent, $storeId, 'attributes_');
                continue;
            }

            //Push to Push API of Coveo
            $updateSourceStatus = True;
            $deleteOlder = False;
            // Setup the push client
            try {
            $push = new Push($this->config->getApiSource(), $this->config->getApiOrg(), $this->config->getApiKeyIndex(), $this->config->getApiBaseUrl(), $this->logger);

            // First remove the old content
            $push->RemoveSingleDocument($this->config->getApiBase().''.$storeId.'/',null,null,True);
            sleep(5);
            $useVariantsAsProducts = False;
            $useVariantsAsProducts = $this->config->getUseVariantAsProduct();
            //Start the push
            $push->Start($updateSourceStatus, $deleteOlder);
            foreach ($catalogData as $dataentry) {
              $mydoc = new Document($dataentry['documentId'],$this->logger);
              $mydoc->AddMetadata('foldingcollection',$dataentry['sku']);
              $mydoc->AddMetadata('foldingcollection',$dataentry['sku']);
              $mydoc->AddMetadata('foldingparent',$dataentry['sku']);
              //$mydoc->AddMetadata('permanentid',$dataentry['sku']);
              $mydoc->permanentid = $dataentry['sku'];
              $alltext = '';
              foreach ($dataentry as $key => $value){
                //encode the $value
                //error_log($key.'==>'.$value);
                if ($key!='documentId' && $key!='variants') {
                  $value = str_replace('\"', '\""', $value);
                  $alltext = $alltext.' '.$value;
                  $value = mb_convert_encoding($value, 'UTF-8', mb_detect_encoding($value, 'UTF-8, ISO-8859-1', true));
                  $mydoc->AddMetadata($key,$value);
                }
              }
              //If useVariantsAsProducts is False, do index the product, else skip this
              if ($useVariantsAsProducts==False || ($dataentry['variants']==null)) {
                $content = "<meta charset='UTF-16'><meta http-equiv='Content-Type' content='text/html; charset=UTF-16'><html><head></head><body>".$alltext."</body></html>";
                $mydoc->SetContentAndZLibCompress($content);
                $push->Add($mydoc);
              }

              //Check if we need to add variants
              if (array_key_exists('variants',$dataentry) && $dataentry['variants']!=null) {
                foreach($dataentry['variants'] as $variant){
                  $mydocv = new Document($dataentry['documentId'].'&var='.$variant['sku'],$this->logger);
                  $mydocv->AddMetadata('store_id',$dataentry['store_id']);
                  //$alltext = 'Product content:<BR>';
                  if ($useVariantsAsProducts==False) {
                     //Treat it as a normal Variant
                     $mydocv->AddMetadata('objecttype','Variant');
                     $mydocv->AddMetadata('foldingcollection',$dataentry['sku']);
                     $mydocv->AddMetadata('foldingparent',$dataentry['sku']);
                     $mydocv->AddMetadata('foldingchild',$variant['sku']);
                     $mydocv->permanentid = $variant['sku'];
                       } else {
                     //Treat as a product
                     $mydocv->AddMetadata('objecttype','Product');
                     $mydocv->permanentid = $variant['sku'];
                     //Take the metadata from the Product
                     foreach ($dataentry as $key => $value){
                      //encode the $value
                      //error_log($key.'==>'.$value);
                      if ($key!='documentId' && $key!='variants') {
                        $value = str_replace('\"', '\""', $value);
                        //We do not want product content in our preview
                        //$alltext = $alltext.' '.$value;
                        $value = mb_convert_encoding($value, 'UTF-8', mb_detect_encoding($value, 'UTF-8, ISO-8859-1', true));
                        $mydocv->AddMetadata($key,$value);
                      }
                    }
                    }
                    //$alltext = $alltext.' <BR>'.'Variant content:<BR>';
                  //Add meta from Variant
                  foreach ($variant as $key => $value){
                    //encode the $value
                    if ($key!='documentId' && $key!='variants') {
                      $value = str_replace('\"', '\""', $value);
                      $alltext = $alltext.' '.$value;
                      $value = mb_convert_encoding($value, 'UTF-8', mb_detect_encoding($value, 'UTF-8, ISO-8859-1', true));
                      $mydocv->AddMetadata($key,$value);
                    }
                  }
                  $content = "<meta charset='UTF-16'><meta http-equiv='Content-Type' content='text/html; charset=UTF-16'><html><head></head><body>".$alltext."</body></html>";
                  $mydocv->SetContentAndZLibCompress($content);
                  $push->Add($mydocv);
    
                }

              }
          
            }

            //End the Push
            $push->End($updateSourceStatus, $deleteOlder);
            } catch (\Exception $e) {
              $this->logger->error($e->getMessage());
              return false;
            }

            /*
            $client = $this->getClient();
            try{
                $indexResult = $client->index([
                    'magento_catalog.csv' => $catalogJsonContent
                    /*,
                    'magento_attributes.csv' => $attributeCsvContent*/
              /*  ], [
                    'PUSH_KEY' => $this->indexerConfig->getApiKeyIndex(),
                    'PUSH_URL' => $this->indexerConfig->getApiBaseUrl()
                ]);
                if ($indexResult->isValid() === false) {
                    $this->logger->error($indexResult->getErrorMessage());
                    return false;
                }
            }catch (\Coveo\SDK\Exception $e){
                $this->logger->error($e->getMessage());
                return false;
            }*/

            $this->logger->debug("[catalog export] Store $storeId catalog data successfully exported!");
        }

        $this->logger->info('[catalog export] All stores catalog data successfully exported!');
        return true;
    }

    /**
     * @inheritdoc
     */
    public function sendStock()
    {
        $storeIds = $this->indexerConfig->getStores();

        $this->logger->info('[stock export] Start exporting stock data for stores ' . implode(',', $storeIds) . '..');

        foreach ($storeIds as $storeId) {
            $this->logger->debug("[stock export] Start exporting catalog data for store $storeId");

            $data = $this->stockIndexFlat->extractData(); //NOTE: product's stock values are global
            if ($data === null) {
                return false;
            }
            $csvContent = $this->arrayToCsv($data);

            if ($this->indexerConfig->isDryRunModeEnabled()) {
                $this->writeCsvToFile($csvContent, $storeId, 'stock_');
                continue;
            }

            //Star
            $client = $this->getClient();
            try{
                $indexResult = $client->index($csvContent, [
                    'ACCESS_KEY_ID' => $this->indexerConfig->getAwsAccessKey(),
                    'SECRET_KEY' => $this->indexerConfig->getAwsSecretKey(),
                    'BUCKET' => $this->indexerConfig->getAwsBucketName(),
                    'PATH' => $this->indexerConfig->getAwsBucketPath(),
                ]);
                if ($indexResult->isValid() === false) {
                    $this->logger->error($indexResult->getErrorMessage());
                    return false;
                }
            }catch (\Coveo\SDK\Exception $e){
                $this->logger->error($e->getMessage());
                return false;
            }

            $this->logger->debug("[stock export] Store $storeId stock data successfully exported!");
        }

        $this->logger->info('[stock export] All stores stock data successfully exported!');

        return true;
    }

    /**
     * Get Client
     *
     * @return \Coveo\SDK\Client
     */
    protected function getClient()
    {
        return $this->clientBuilder
            ->withApiKey($this->config->getApiKeyIndex())
            ->withLogger($this->logger)
            ->build();
    }

    /**
     * Convert array into CSV value
     *
     * @param array $array
     * @return string
     */
    protected function arrayToCsv($array)
    {
        $f = fopen('php://memory', 'rb+');
        foreach ($array as $fields) {
            fputcsv($f, $fields, self::CSV_SEPARATOR);
        }
        rewind($f);
        $csvContent = stream_get_contents($f);
        fclose($f);
        $csvContent = rtrim($csvContent);
        $csvContent = str_replace('\"', '\""', $csvContent); // Fix JSON interpolation for "
        return mb_convert_encoding($csvContent, 'UTF-8', mb_detect_encoding($csvContent, 'UTF-8, ISO-8859-1', true));
    }

    protected function cleanJSON($json){
      $source = json_encode($json);
      //Debug($source);
      //error_log($source);
      $result = preg_replace('/,\s*"[^"]+": ?null|"[^"]+": ?null,?/', '', $source);
      $result = preg_replace('/,\s*"[^"]+": ?\[\]|"[^"]+": ?\[\],?/', '', $result);
      //Debug($result);
      return $result;
    }

     /**
     * Convert array into JSON value
     *
     * @param array $array
     * @return string
     */
    protected function arrayToJson($array)
    {
      return $this->cleanJSON($array);
      /*
        $f = fopen('php://memory', 'rb+');
        foreach ($array as $fields) {
            fputcsv($f, $fields, self::CSV_SEPARATOR);
        }
        rewind($f);
        $csvContent = stream_get_contents($f);
        fclose($f);
        $csvContent = rtrim($csvContent);
        $csvContent = str_replace('\"', '\""', $csvContent); // Fix JSON interpolation for "
        return mb_convert_encoding($csvContent, 'UTF-8', mb_detect_encoding($csvContent, 'UTF-8, ISO-8859-1', true));*/
    }

    /**
     * Write CSV file into var directory
     *
     * @param string $csvContent
     * @param integer $storeId
     * @param string $prefix
     */
    protected function writeCsvToFile($csvContent, $storeId, $prefix = '')
    {
        try {
            $varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $varDirectory->writeFile("coveo/${prefix}${storeId}.json", $csvContent);
        }catch (\Magento\Framework\Exception\FileSystemException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
