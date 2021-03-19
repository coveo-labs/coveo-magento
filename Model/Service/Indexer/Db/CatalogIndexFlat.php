<?php declare(strict_types=1);
namespace Coveo\Search\Model\Service\Indexer\Db;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Coveo\Search\Api\Service\LoggerInterface;
use Coveo\Search\Api\Service\ConfigInterface;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

class CatalogIndexFlat
{
    /**
     * Table name
     */
    const TABLE_NAME = 'coveo_catalog_flat';
 /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SerializerJson
     */
    protected $serializerJson;

    /**
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     * @param SerializerJson $serializerJson
     * @param ConfigInterface $config
     */
    public function __construct(
        ResourceConnection $resource,
        LoggerInterface $logger,
        SerializerJson $serializerJson,
        ConfigInterface $config
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->logger = $logger;
        $this->serializerJson = $serializerJson;
        $this->config = $config;
    }

    /**
     * Store data into database flat table
     *
     * @param array $data
     * @param integer $storeId
     * @return boolean
     * @throws \Exception
     */
    public function storeData($data, $storeId)
    {
        $updateTime = new \DateTime();
        $updateTimeStr = $updateTime->format('Y-m-d H:i:s');
        $data = array_map(function($item) use ($storeId, $updateTimeStr) {
            $sku = isset($item['sku']) ? $item['sku'] : 'undefined';
            $this->logger->info('[Reindex catalog] Adding SKU '.$sku);
            //Add necessary fields to item
            $item['documentId']=$this->config->getApiBase().''.$storeId.'/?sku='.$sku;
            /*if ($item['variants']!=null) {
              //$item['variants'] = json_encode($item['variants']);
            }*/
            $item['store_id']=$storeId;
            $item['objecttype']='Product';
            return [
              
                'store_id' => $storeId,
                'sku' => $sku,
                'data' => $this->serializerJson->serialize($item),
                'update_time' => $updateTimeStr
            ];
        }, $data);
        //We need to add one more array to data, for the deleteChildren feature
        $mainDoc=array();
        $mainDoc['documentId']=$this->config->getApiBase().''.$storeId.'/';
        $mainDoc['store_id']= $storeId;
        $mainDoc['objecttype']='Store';
        $mainDoc['sku']='';
        $mainDoc['variants']=null;
        $mainEntry = array();
        $mainEntry['store_id']=$storeId;
        $mainEntry['sku']='';
        $mainEntry['data'] = $this->serializerJson->serialize($mainDoc);
        $mainEntry['update_time'] = $updateTimeStr;
        array_unshift($data,$mainEntry);
        $this->logger->info('[Reindex catalog] Store data, begin transaction');

        $this->connection->beginTransaction();
        try {
            $tableName = $this->resource->getTableName(self::TABLE_NAME);
            $this->logger->info('[Reindex catalog] Store data, in table: '.$tableName);

            $this->connection->insertOnDuplicate($tableName, $data);
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            $this->logger->error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Extract data from database flat table
     *
     * @param integer $storeId
     * @param array $headers
     * @return array|null
     */
    public function extractData($storeId, $headers)
    {
        try {
            $tableName = $this->resource->getTableName(self::TABLE_NAME);
            $select = $this->connection->select()->from($tableName)->where('store_id = ?', $storeId);
            $data = $this->connection->query($select)->fetchAll();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return null;
        }

        if (sizeof($data) === 0) {
            $this->logger->error('No data into '.self::TABLE_NAME.', reindex is required');
            return [];
        }
        //Get rid of the columns returned by the query in the JSON
        /*return array_merge([$headers], array_map(function ($item) use ($headers){
            $unSerializedItem = $this->serializerJson->unserialize($item['data']);
            $resultItem = [];
            foreach ($headers as $header) {
                $resultItem[$header] = isset($unSerializedItem[$header]) ? $unSerializedItem[$header] : null;
            }
            return $resultItem;
        }, $data));*/
        return array_map(function ($item) use ($headers){
          $unSerializedItem = $this->serializerJson->unserialize($item['data']);
          $resultItem = [];
          foreach ($unSerializedItem as $key => $value) {
            $resultItem[$key] = $value;
          }
          /*foreach ($headers as $header) {
              $resultItem[$header] = isset($unSerializedItem[$header]) ? $unSerializedItem[$header] : null;
          }*/
          return $resultItem;
      }, $data);
    }

    /**
     * Delete all data from flat table
     *
     * @param integer|null $storeId
     * @return boolean
     */
    public function truncateData($storeId = null)
    {
        $tableName = $this->resource->getTableName(self::TABLE_NAME);

        if ($storeId === null) {
            try {
                $this->connection->truncateTable($tableName);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                return false;
            }
            return true;
        }

        try {
            $this->connection->delete($tableName, ['store_id' => $storeId]);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
        return true;
    }
}
