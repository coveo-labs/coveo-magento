<?php declare(strict_types=1);
namespace Coveo\Search\Model\Service\Indexer\Enricher;

use Coveo\Search\Api\Service\Indexer\EnricherInterface;
use Coveo\Search\Api\Service\Config\IndexerConfigInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class AttributesEnricher implements EnricherInterface
{
    /**
     * @var IndexerConfigInterface
     */
    protected $indexerConfig;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * AttributesEnricher constructor.
     *
     * @param IndexerConfigInterface $indexerConfig
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        IndexerConfigInterface $indexerConfig,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->indexerConfig = $indexerConfig;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute($data,$attributesCollection)
    {
        $ids = array_map(function($elem) {
            return $elem['id'];
        }, $data);

        $attributes = $this->indexerConfig->getAttributesWithoutCustoms();

        $productsCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('sku')
            ->addFieldToFilter('entity_id', $ids);

        foreach ($attributes as $attribute) {
            $productsCollection->addAttributeToSelect($attribute);
            //error_log($attribute);
        }

        //for each attribute
        //attribute, get all values
        //fill in data for each product
        foreach($attributes as $attribute) {
          $foundAttr=False;
          //Check if we have lookup value or just a normal attribute
          foreach ($attributesCollection as $attributeCheck) {
            $attcheck = $attributeCheck->getData()['attribute_code'];
            //error_log('Finding attribute: '.$attribute.'==>'.$att);
            if ($attribute == $attcheck) {
              $foundAttr = True;
            
        
          $options = $attributeCheck->getSource()->getAllOptions();
          $att = $attributeCheck->getData()['attribute_code'];
          if (count($options)>0) {
            //We have a lookup attribute
            foreach ($productsCollection as $product) {
              $resolved = '';
              $dataIndex = array_search($product->getId(), $ids, true);
              if ($dataIndex === -1) {
                  continue; // this shouldn't happen
              }
              $data[$dataIndex][$att] = $product->getData($att);
              //error_log('Found att with lookup - '.$att.': '.$data[$dataIndex][$att]);
              if (isset($data[$dataIndex][$att]) && array_key_exists($att,$data[$dataIndex])){
              if ($data[$dataIndex][$att]!=null) {  
                $values = explode(',',$data[$dataIndex][$att]);
                foreach($values as $value){
                  if (is_numeric($value)) {
                    foreach ($options as $option){
                      if ($option['value']==$value) {
                        //error_log('Found value: '.$option['label']);
                        if ($resolved == '') {
                          $resolved = $option['label'];
                        } else {
                        $resolved = $resolved.';'.$option['label'];
                        }
                      }
                    }
                  }
                }
              }
            }
              if ($resolved!=='') {
                $data[$dataIndex][$att] = $resolved;
                //error_log('After resolved:'.$data[$dataIndex][$att]);
              }
              //Check if variants needs to be resolved
              if ($data[$dataIndex]['variants']!=null) {
                //error_log('Found variants - '.$att.': '.json_encode($data[$dataIndex]['variants']));
                foreach($data[$dataIndex]['variants'] as &$variant){
                  //error_log('Found variant: '.json_encode($variant));
                  $resolved = '';
                  if (isset($variant[$att]) && array_key_exists($att,$variant)){
                  if ($variant[$att]!=null) {  
                    $values = explode(',',$variant[$att]);
                    foreach($values as $value){
                      if (is_numeric($value)) {
                        foreach ($options as $option){
                          if ($option['value']==$value) {
                            //error_log('Found value: '.$option['label']);
                            if ($resolved == '') {
                              $resolved = $option['label'];
                            } else {
                            $resolved = $resolved.';'.$option['label'];
                            }
                          }
                        }
                      }
                    }
                  }
                  if ($resolved!=='') {
                    $variant[$att] = $resolved;
                    //error_log('After resolved variant value:'.$variant[$att]);
                  }
                }
                }
              }
            }
          } else {
            //Normal attribute
            foreach ($productsCollection as $product) {
              $dataIndex = array_search($product->getId(), $ids, true);
              if ($dataIndex === -1) {
                  continue; // this shouldn't happen
              }  
              
              $data[$dataIndex][$att] = $product->getData($att);
              //error_log('Found att - '.$att.': '.$data[$dataIndex][$att]);
            }
          }
        }
      }
      if ($foundAttr==False) {
        //Normal attribute
        foreach ($productsCollection as $product) {
          $dataIndex = array_search($product->getId(), $ids, true);
          if ($dataIndex === -1) {
              continue; // this shouldn't happen
          }  
          
          $data[$dataIndex][$attribute] = $product->getData($attribute);
          //error_log('Found NORMAL att - '.$attribute.': '.$data[$dataIndex][$attribute]);
        }
      }
    }
      
        /*foreach ($productsCollection as $product) {
            $dataIndex = array_search($product->getId(), $ids, true);
            if ($dataIndex === -1) {
                continue; // this shouldn't happen
            }
            //error_log('   Data: '.json_encode($data[$dataIndex]));
            array_walk($attributes, function ($attribute) use($dataIndex, $product, &$data, $attributesCollection) {
              //error_log($attribute);
              //error_log($attribute.'   Before:'.$data[$dataIndex][$attribute]);
              //error_log('Got product data: '.$product->getData($attribute));
                $data[$dataIndex][$attribute] = $product->getData($attribute);
                $resolved = '';
                //Check if it is an ID or array
                if ($data[$dataIndex][$attribute]!=null) {
                if (is_numeric($data[$dataIndex][$attribute]) || strpos($data[$dataIndex][$attribute],',',1)<8){
                  $values = explode(',',$data[$dataIndex][$attribute]);
                  array_walk($values, function($value) use ($dataIndex,$attribute, $attributesCollection, &$resolved){
                     if (is_numeric($value)) {
                      //error_log('Numeric value to find: '.$value);
                      foreach ($attributesCollection as $attributeCheck) {
                        $att = $attributeCheck->getData()['attribute_code'];
                        //error_log('Finding attribute: '.$attribute.'==>'.$att);
                        if ($attribute == $att)
                        {
                        $options = $attributeCheck->getSource()->getAllOptions();
                        //error_log('Found attribute: '.$att);
        
                        foreach ($options as $option){
                          if ($option['value']==$value) {
                            //error_log('Found value: '.$option['label']);
                            if ($resolved == '') {
                              $resolved = $option['label'];
                            } else {
                            $resolved = $resolved.';'.$option['label'];
                            }
                          }
                        }
                      }
                    }
                     }
                  });
                }
              }
                if ($resolved!=='') {
                  $data[$dataIndex][$attribute] = $resolved;
                  error_log('After resolved:'.$data[$dataIndex][$attribute]);
                }
                //error_log('After:'.$data[$dataIndex][$attribute]);
            });
        }*/

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getEnrichedKeys()
    {
        return $this->indexerConfig->getAttributesWithoutCustoms();
    }
}
