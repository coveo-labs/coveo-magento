<?php
// -------------------------------------------------------------------------------------
// CoveoPush
// -------------------------------------------------------------------------------------
// Contains the CoveoPush class
//   Can push documents, update securities
// -------------------------------------------------------------------------------------
namespace Coveo\Search\SDK\SDKPushPHP;

use Coveo\Search\SDK\SDKPushPHP\Constants;
use Coveo\Search\SDK\SDKPushPHP\Document;
use Coveo\Search\SDK\SDKPushPHP\Permissions;
use Coveo\Search\Api\Service\LoggerInterface;


class LargeFileContainer{
    //"""Class to store the properties returned by LargeFile Container call """
    // The secure URI used to upload the item data into an Amazon S3 file.
    public $UploadUri = '';

    // The file identifier used to link the uploaded data to the pushed item.
    // This value needs to be set in the item 'CompressedBinaryDataFileId' metadata.
    public $FileId = '';

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Default constructor used by the deserialization.
    function __construct( array $p_JSON){
        $this->UploadUri = $p_JSON['uploadUri'];
        $this->FileId = $p_JSON['fileId'];
    }
}


class Push{
    /*"""
    class Push.
    Holds all methods to start pushing data.

    3 methods of pushing data:
    A) Push a single document
       Usage: When you simply need push a single document once in a while
       NOT TO BE USED: When you need to update a lot of documents. Use Method C or Method B for that.
        
      require_once('../coveopush/CoveoConstants.php');
      require_once('../coveopush/CoveoDocument.php');
      require_once('../coveopush/CoveoPermissions.php');
      require_once('../coveopush/CoveoPush.php');
      require_once('../coveopush/Enum.php');

      $sourceId = 'xx';
      $orgId = 'xx';
      $apiKey = 'xx';

      // Setup the push client
      $push = new Coveo\SDKPushPHP\Push($sourceId, $orgId, $apiKey);

      //$push->UpdateSourceStatus(Coveo\SDKPushPHP\SourceStatusType::Rebuild);


      // Create a document
      $mydoc = new Coveo\SDKPushPHP\Document("https://myreference/doc2");
      $mydoc->SetData("This is document Two");
      $mydoc->FileExtension = ".html";
      $mydoc->AddMetadata("authors", "jdst@coveo.com");
      $mydoc->Author = "Wim";
      $mydoc->Title = "What's up Doc 2?";
      // Push the document
      $push->AddSingleDocument($mydoc);

    B) Push a batch of documents in a single call
       Usage: When you need to upload a lot of (smaller) documents
       NOT TO BE USED: When you need to update a lot of LARGE documents. Use Method C for that.

       // Setup the push client
        $push = new Coveo\SDKPushPHP\Push($sourceId, $orgId, $apiKey);
        // Create a batch of documents
        $batch=array(
            createDoc('/testfiles/BigExample.pdf'),
            createDoc('/testfiles/BigExample2.pptx'));

        // Push the documents
        $push->AddDocuments($batch, array(), $updateSourceStatus, $deleteOlder);


    C) RECOMMENDED APPROACH: Push a batch of documents, document by document
       Usage: When you need to upload a lot of smaller/and or larger documents
       NOT TO BE USED: When you have a single document. Use Method A for that.

        // Setup the push client
        $push = new Coveo\SDKPushPHP\Push($sourceId, $orgId, $apiKey);
        // Start the batch
        $push->Start($updateSourceStatus, $deleteOlder);
        // Set the maximum
        $push->SetSizeMaxRequest(150*1024*1024);

        $push->Add(createDoc('/testfiles/Large1.pptx', '1'));
        $push->Add(createDoc('/testfiles/Large2.pptx', '1'));
        $push->Add(createDoc('/testfiles/Large1.pptx', '2'));
        $push->Add(createDoc('/testfiles/Large2.pptx', '2'));
        $push->Add(createDoc('/testfiles/Large1.pptx', '3'));
        $push->Add(createDoc('/testfiles/Large2.pptx', '3'));
        $push->Add(createDoc('/testfiles/Large1.pptx', '4'));
        $push->Add(createDoc('/testfiles/Large2.pptx', '4'));
        $push->Add(createDoc('/testfiles/Large1.pptx', '5'));
        $push->Add(createDoc('/testfiles/Large2.pptx', '5'));

        # End the Push
        $push->End($updateSourceStatus, $deleteOlder);


    """*/
    public $SourceId = '';
    public $OrganizationId = '';
    public $ApiKey = '';
    public $PushApiEndpoint = PushApiEndpoint::PROD_PUSH_API_URL;
    public $ProcessingDelayInMinutes = 0;
    public $StartOrderingId = 0;
    public $totalSize = 0;
    public $ToAdd = array();
    public $ToDel = array();
    public $BatchPermissions = array();
    public $MaxRequestSize = 0;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Default constructor used by the deserialization.
    function __construct(string $p_SourceId, string $p_OrganizationId, string $p_ApiKey, string $p_Endpoint=null,$logger){
        /*"""
        Push Constructor.
        :arg p_SourceId: Source Id to use
        :arg p_OrganizationId: Organization Id to use
        :arg p_ApiKey: API Key to use
        :arg p_Endpoint: Constants.PushApiEndpoint
        """*/
        set_time_limit ( 3000 );
        if ($p_Endpoint==null) {
            $p_Endpoint = PushApiEndpoint::PROD_PUSH_API_URL;
        }
        $this->SourceId = $p_SourceId;
        $this->OrganizationId = $p_OrganizationId;
        $this->ApiKey = $p_ApiKey;
        $this->Endpoint = $p_Endpoint;
        $this->MaxRequestSize = 255052544;
        $this->logger = $logger;

        // validate Api Key
        $valid=preg_match('/^\w{10}-\w{4}-\w{4}-\w{4}-\w{12}$/', $p_ApiKey, $matches);
        if ($valid==0){
            $this->logger->debug("Invalid Api Key format");
            return;
        }

        $this->logger->info('Pushing to source ' . $p_SourceId);
    }

    

    function cleanJSON($json){
      $source = json_encode($json);
      //$this->logger->debug($source);
      $result = preg_replace('/,\s*"[^"]+": ?null|"[^"]+": ?null,?/', '', $source);
      $result = preg_replace('/,\s*"[^"]+": ?\[\]|"[^"]+": ?\[\],?/', '', $result);
      //$this->logger->debug($result);
      return $result;
    }


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function SetSizeMaxRequest(int $p_Max){
        /*"""
        SetSizeMaxRequest.
        By default MAXIMUM_REQUEST_SIZE_IN_BYTES is used (256 Mb)
        :arg p_Max: Max request size in bytes
        """*/
        if ($p_Max > Constants::MAXIMUM_REQUEST_SIZE_IN_BYTES){
          $this->logger->debug("SetSizeMaxRequest: to big");
            return;
            
        }

        $this->MaxRequestSize = $p_Max;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function GetSizeMaxRequest(){
        if ($this->MaxRequestSize > 0) {
            return $this->MaxRequestSize;
        }

        return Constants::MAXIMUM_REQUEST_SIZE_IN_BYTES;
    }


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function GetRequestHeaders(){
        /*"""
        GetRequestHeaders.
        Gets the Request headers needed for every Push call.
        """*/

        $this->logger->debug('GetRequestHeaders');
        $content = array();
        $content['Authorization']='Bearer ' . $this->ApiKey;
        $content['Content-Type']='application/json';
        return ($content);
        
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    function GetRequestHeadersForS3(){
        /*"""
        GetRequestHeadersForS3.
        Gets the Request headers needed for calls to Amazon S3.
        """*/
        $this->logger->debug('GetRequestHeadersForS3');
        $content = array();
        $content['Content-Type']='application/octet-stream';
        $content[HttpHeaders::AMAZON_S3_SERVER_SIDE_ENCRYPTION_NAME]=HttpHeaders::AMAZON_S3_SERVER_SIDE_ENCRYPTION_VALUE;
        
        return ($content);
    }

    function createPath($myEndpoint=null){
      if ($myEndpoint==null) {
        $myEndpoint = $this->Endpoint;
      }
        $values = array();
        $values['endpoint']=$myEndpoint;
        $values['org_id'] = $this->OrganizationId;
        $values['src_id'] = $this->SourceId;
        $values['prov_id'] = '';
        return $values;
    }
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function GetStatusUrl() {
        /*"""
        GetStatusUrl.
        Get the URL to update the Status of the source call
        """*/
        $this->logger->debug('GetStatusUrl');
        $values = $this->createPath();
        $url = replacePath( PushApiPaths::SOURCE_ACTIVITY_STATUS, $values);
        
        $this->logger->debug($url);
        return $url;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function CreateOrderingId() {
        /*"""
        CreateOrderingId.
        Create an Ordering Id, used to set the order of the pushed items
        """*/
        $this->logger->debug('CreateOrderingId');
        $ordering_id = round((microtime(true)*1000),0);
        $this->logger->debug($ordering_id);
        return $ordering_id;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function GetLargeFileContainerUrl(){
        /*"""
        GetLargeFileContainerUrl.
        Get the URL for the Large File Container call.
        """*/
        $this->logger->debug('GetLargeFileContainerUrl');
        $values = $this->createPath();
        $url = replacePath( PushApiPaths::DOCUMENT_GET_CONTAINER, $values);
        $this->logger->debug($url);
        return $url;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function GetUpdateDocumentUrl(){
        /*"""
        GetUpdateDocumentUrl.
        Get the URL for the Update Document call.
        """*/
        $this->logger->debug('GetUpdateDocumentUrl');
        $values = $this->createPath();
        $url = replacePath( PushApiPaths::SOURCE_DOCUMENTS, $values);
        
        $this->logger->debug($url);
        return $url;
    }
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function GetSecurityProviderUrl(string $p_Endpoint, string $p_SecurityProviderId){
        /*"""
        GetSecurityProviderUrl.
        Get the URL to create the security provider
        """*/
        $this->logger->debug('GetSecurityProviderUrl');
        $values = $this->createPath($p_Endpoint);
        $values['prov_id'] = $p_SecurityProviderId;
        $url = replacePath( PlatformPaths::CREATE_PROVIDER, $values);
        $this->logger->debug($url);
        return $url;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function GetDeleteDocumentUrl() {
        /*"""
        GetDeleteDocumentUrl.
        Get the URL for the Delete Document call.
        """*/
        $this->logger->debug('GetDeleteDocumentUrl');
        $values = $this->createPath();
        $url = replacePath( PushApiPaths::SOURCE_DOCUMENTS, $values);
        $this->logger->debug($url);
        return $url;
    }


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function GetUpdateDocumentsUrl(){
        /*"""
        GetUpdateDocumentsUrl.
        Get the URL for the Update Documents (batch) call.
        """*/
        $this->logger->debug('GetUpdateDocumentsUrl');
        $values = $this->createPath();
        $url = replacePath( PushApiPaths::SOURCE_DOCUMENTS_BATCH, $values);
        $this->logger->debug($url);
        return $url;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function GetDeleteOlderThanUrl(){
        /*"""
        GetDeleteOlderThanUrl.
        Get the URL for the Delete Older Than call.
        """*/
        $this->logger->debug('GetDeleteOlderThanUrl');
        $values = $this->createPath();
        $url = replacePath( PushApiPaths::SOURCE_DOCUMENTS_DELETE, $values);
        $this->logger->debug($url);
        return $url;
    }

    function GetUrl(string $path, string $prov_id = '') {
        /*"""
        Return path with values (endpoint, org, source, provider) set accordingly.
        """*/
        $this->logger->debug('GetUrl');
        $values = $this->createPath();
        $values['prov_id'] = $prov_id;
        $url = replacePath( $path, $values);
        $this->logger->debug($url);
        return $url;
    }


    function CheckReturnCode( $p_Response){
        /*"""
        CheckReturnCode.
        Checks the return code of the response (from the request object).
        If not valid an error will be raised.
        :arg p_Response: response from request
        """*/
        $this->logger->debug($p_Response['status_code']);
        if ($p_Response['status_code'] == 403) {
            $this->logger->debug('Check privileges on your Api key.');
            return;
        }

        if ($p_Response['status_code'] >= 300){
            $this->logger->debug($p_Response['text']);
            return;
        }

        //p_Response.raise_for_status()

        return $p_Response['status_code'];
    }

    function arrayToStr( array $process){
      $newstring='';
      foreach ($process as $key => $value){
        $newstring .= $key.':'. $value."\r\n";
      }
      return $newstring;
    }

    function doPost($url, $headers,array $postdata){
      $this->logger->debug('doPost');
        $headers = array_merge( $headers, array('Connection'=>'close'));
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => $this->arrayToStr($headers)
            )
        );
        if ($postdata !== null) {
            $params = http_build_query($postdata);
            //$opts['http']['content'] = $params;
            $url .= '?' . $params;
        }        

        $context = stream_context_create($opts);
        $result = file_get_contents(($url), false, $context);
        if ($result === FALSE) {
            $this->logger->debug('POST Request failed: '.$url);
            return False;
        } else {
            $json = json_decode($result, TRUE);
            return $json;
        }
    }

    function doPut($url, $headers, $data, $params=null){
      $this->logger->debug('doPut');
      $headers = array_merge( $headers, array('Connection'=>'close'));
      $opts = array('http' =>
            array(
                'method'  => 'PUT',
                'header'  => $this->arrayToStr($headers)
            )
        );
        if ($data!==null) {
            $opts['http']['content'] = $data;
        }
        if ($params !== null) {
            $params = http_build_query($params);
            $url .= '?' . $params;
        }
        $context = stream_context_create($opts);
        //echo($url);
        //echo(json_decode($opts));
        $result = file_get_contents(($url), false, $context);
        if ($result === FALSE) {
            $this->logger->debug('PUT Request failed: '.$url);
            return False;
        } else {
            $json = json_decode($result, TRUE);
            return $json;
        }
    }

    function doDelete($url, $headers, $params=null, $data=null){
      $this->logger->debug('doDelete');
      $headers = array_merge( $headers, array('Connection'=>'close'));
        
        $opts = array('http' =>
            array(
                'method'  => 'DELETE',
                'header'  => $this->arrayToStr($headers)
            )
        );
        if ($data!==null) {
            $opts['http']['content'] = $data;
        }
        if ($params !== null) {
            $params = http_build_query($params);
            $url .= '?' . $params;
            $this->logger->debug($url);
        }
        $context = stream_context_create($opts);
        
        $result = file_get_contents(($url), false, $context);
        //echo 'doDelete';
        if ($result === FALSE) {
            $this->logger->debug('DELETE Request failed: '.$url);
            return False;
        } else {
          //echo json_decode($result, TRUE);
            $json = json_decode($result, TRUE);
            return $json;
        }
    }
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function UpdateSourceStatus( string $p_SourceStatus){
        /*"""
        UpdateSourceStatus.
        Update the Source status, so that the activity on the source reflects what is going on
        :arg p_SourceStatus: Constants.SourceStatusType (REBUILD, IDLE)
        """*/

        $this->logger->debug('UpdateSourceStatus, Changing status to ' . $p_SourceStatus);
        $params = array( Parameters::STATUS_TYPE => $p_SourceStatus);
        
        $result = $this->doPost($this->GetStatusUrl(), $this->GetRequestHeaders(), $params);
        if ($result!=False) {
            return true;
        }
        else {
            return false;
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function GetLargeFileContainer(){
        /*"""
        GetLargeFileContainer.
        Get the S3 Large Container information.
        returns: LargeFileContainer Class
        """*/

        $this->logger->debug('GetLargeFileContainer ' . $this->GetLargeFileContainerUrl());
        $params = array();
        $result = $this->doPost($this->GetLargeFileContainerUrl(), $this->GetRequestHeaders(), $params);
        if ($result!=False) {
            $results = new LargeFileContainer($result);
            return $results;
        }
        else {
            return null;
        }
    }

    function isBase64($s){
      /*"""
      isBase64.
      Checks if string is base64 encoded.
      Returns True/False
      """*/
      try{
          return base64_encode(base64_decode($s)) == $s;
      }
      catch(Exception $e){
          return False;
      }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function UploadDocument(string $p_UploadUri, string $p_CompressedFile){
        /*"""
        UploadDocument.
        Upload a document to S3.
        :arg p_UploadUri: string, retrieved from the GetLargeFileContainer call
        :arg p_CompressedFile: string, Properly compressed file to upload as contents
        """*/

        $this->logger->debug('UploadDocument '.$p_UploadUri);

        if ($p_UploadUri==null){
            $this->logger->debug("UploadDocument: p_UploadUri is not present");
            return;
        }
        if ($p_CompressedFile==null){
            $this->logger->debug("UploadDocument: p_CompressedFile is not present");
            return;
        }

        // Check if p_CompressedFile is base64 encoded, if so, decode it first
        if ($this->isBase64($p_CompressedFile)){
            $p_CompressedFile = base64_decode($p_CompressedFile);
        }
        $result = $this->doPut( $p_UploadUri, $this->GetRequestHeadersForS3(), $p_CompressedFile);
        if ($result!=False) {
            return true;
        }
        else {
            return false;
        }
    }


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function UploadDocuments(string $p_UploadUri, array $p_ToAdd, array $p_ToDelete){
        /*"""
        UploadDocuments.
        Upload a batch document to S3.
        :arg p_UploadUri: string, retrieved from the GetLargeFileContainer call
        :arg p_ToAdd: list of CoveoDocuments to add
        :arg p_ToDelete: list of CoveoDocumentToDelete to delete
        """*/

        $this->logger->debug('UploadDocuments '.$p_UploadUri);

        if ($p_UploadUri==null){
            $this->logger->debug("UploadDocument: p_UploadUri is not present");
            return;
        }
        if ($p_ToAdd==null && $p_ToDelete==null) {
            $this->logger->debug("UploadBatch: p_ToAdd and p_ToDelete are empty");
            return;
        }

        $data = new BatchDocument();
        $data->AddOrUpdate = $p_ToAdd;
        $data->Delete = $p_ToDelete;
        error_log(json_encode($data));
        $result = $this->doPut( $p_UploadUri, $this->GetRequestHeadersForS3(), $this->cleanJSON($data));
        if ($result!=False) {
            return true;
        }
        else {
            return false;
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function UploadPermissions(string $p_UploadUri){
        /*"""
        UploadPermissions.
        Upload a batch permission to S3.
        :arg p_UploadUri: string, retrieved from the GetLargeFileContainer call
        """*/

        $this->logger->debug('UploadPermissions '.$p_UploadUri);

        if ($p_UploadUri==null){
            $this->logger->debug("UploadPermissions: p_UploadUri is not present");
            return;
        }

        $permissions = $this->cleanJSON($this->BatchPermissions);
        $this->logger->debug("JSON: " . $permissions);
        $result = $this->doPut( $p_UploadUri, $this->GetRequestHeadersForS3(), $permissions);
        if ($result!=False) {
            return true;
        }
        else {
            return false;
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function GetContainerAndUploadDocument(string $p_Content){
        /*"""
        GetContainerAndUploadDocument.
        Get a Large File Container instance and Upload the document to S3
        :arg p_Content: string, Properly compressed file to upload as contents
        return: S3 FileId value
        """*/

        $this->logger->debug('GetContainerAndUploadDocument');
        $container = $this->GetLargeFileContainer();
        if ($container==null){
            $this->logger->debug("GetContainerAndUploadDocument: S3 container is null");
            return;
        }

        $this->UploadDocument($container->UploadUri, $p_Content);

        return $container->FileId;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    function UploadDocumentIfTooLarge(Document $p_Document){
        /*"""
        UploadDocumentIfTooLarge.
        Uploads an Uncompressed/Compressed Document, if it is to large a S3 container is created, document is being uploaded to s3
        :arg p_Document: Document
        """*/

        $size = strlen ($p_Document->Data)+strlen($p_Document->CompressedBinaryData);
        $this->logger->debug('UploadDocumentIfTooLarge size = ' . $size);

        if ($size > Constants::COMPRESSED_DATA_MAX_SIZE_IN_BYTES){
            $data = '';
            if ($p_Document->Data){
                $data = $p_Document->Data;
            }
            else {
                $data = $p_Document->CompressedBinaryData;
            }

            $fileId = $this->GetContainerAndUploadDocument($data);
            $p_Document->SetCompressedDataFileId($fileId);
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddUpdateDocumentRequest(Document $p_CoveoDocument, int $orderingId=null){
        /*"""
        AddUpdateDocumentRequest.
        Sends the document to the Push API, if previously uploaded to s3 the fileId is set
        :arg p_Document: Document
        :arg orderingId: int (optional)
        """*/
        $this->logger->debug('AddUpdateDocumentRequest');
        $params = array( Parameters::DOCUMENT_ID => $p_CoveoDocument->DocumentId);

        if ($orderingId!=null) {
            $params[Parameters::ORDERING_ID] = $orderingId;
            
        }

        $this->logger->debug(json_encode($params));

        // Set the compression type parameter
        if ($p_CoveoDocument->CompressedBinaryData != '' || $p_CoveoDocument->CompressedBinaryDataFileId != ''){
            $params[Parameters::COMPRESSION_TYPE] = $p_CoveoDocument->CompressionType;
        }

        $body = json_encode($p_CoveoDocument->cleanUp());
        // self.logger.debug(body)
        $result = $this->doPut( $this->GetUpdateDocumentUrl(), $this->GetRequestHeaders(), $body, $params);
        if ($result!=False) {
            return true;
        }
        else {
            return false;
        }
    }


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function DeleteDocument(string $p_DocumentId, int $orderingId=null, bool $deleteChildren = null){
        /*"""
        Deletes the document
        :arg p_DocumentId: CoveoDocument
        :arg orderingId: int
        :arg deleteChildren: bool, if children must be deleted
        """*/
        $this->logger->debug('DeleteDocument');
        if ($deleteChildren==null) {
            $deleteChildren = False;
        }

        $params = array( Parameters::DOCUMENT_ID => $p_DocumentId);

        if ($orderingId!=null) {
            $params[Parameters::ORDERING_ID] = $orderingId;
            
        }

        if ($deleteChildren==True){
            $params[Parameters::DELETE_CHILDREN] = "true";
        }

        $this->logger->debug(json_encode($params));

        $result = $this->doDelete( $this->GetDeleteDocumentUrl(), $this->GetRequestHeaders(), $params);
        if ($result!=False) {
            return true;
        }
        else {
            return false;
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function DeleteOlderThan(float $orderingId=null, int $queueDelay=null){
        /*"""
        DeleteOlderThan.
        All documents with a smaller orderingId will be removed from the index
        :arg orderingId: float
        """*/

        $this->logger->debug('DeleteOlderThan orderingId: '.$orderingId.', queueDelay: '.$queueDelay);
        // Validate
        if ($orderingId <= 0){
            $this->logger->debug("DeleteOlderThan: orderingId must be a positive 64 bit float.");
            return;
        }

        $params = array( Parameters::ORDERING_ID => $orderingId);

        if ($queueDelay!=null){
            if  (!($queueDelay >= 0 && $queueDelay <= 1440)){
                $this->logger->debug("DeleteOlderThan: queueDelay must be between 0 and 1440.");
                return;
            }
            else {
                $params[Parameters::QUEUE_DELAY] = $queueDelay;
            }
        } else {
          $params[Parameters::QUEUE_DELAY] = 0;
        }
        $result = $this->doDelete( $this->GetDeleteOlderThanUrl(), $this->GetRequestHeaders(), $params);
        if ($result!=False) {
            return true;
        }
        else {
            return false;
        }
    }
        

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddSingleDocument(Document $p_CoveoDocument, bool $updateStatus=null, int $orderingId=null){
        /*"""
        AddSingleDocument.
        Pushes the Document to the Push API
        :arg p_CoveoDocument: Document
        :arg p_UpdateStatus: bool (True), if the source status should be updated
        :arg orderingId: int, optional
        """*/

        $this->logger->debug('AddSingleDocument '.$p_CoveoDocument->DocumentId);
        // Single Call
        // First check
        list($valid, $error) = $p_CoveoDocument->Validate();
        if (!$valid){
            $this->logger->debug("AddSingleDocument: ".$error);
            return;
        }

        // Update Source Status
        if ($updateStatus==true || $updateStatus==null){
            $this->UpdateSourceStatus(SourceStatusType::Rebuild);
        }
//$this->logger->debug(json_encode($p_CoveoDocument->cleanUp()));
        // Push Document
        try{
            if ($p_CoveoDocument->CompressedBinaryData != '' || $p_CoveoDocument->Data != ''){
                $this->UploadDocumentIfTooLarge($p_CoveoDocument);
            }
            $this->AddUpdateDocumentRequest($p_CoveoDocument, $orderingId);
        }
        finally{
            $p_CoveoDocument->Content = '';
        }
        // Update Source Status
        if ($updateStatus==true || $updateStatus==null){
            $this->UpdateSourceStatus(SourceStatusType::Idle);
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function RemoveSingleDocument(string $p_DocumentId, bool $updateStatus=null, int $orderingId=null, bool $deleteChildren=null){
        /*"""
        RemoveSingleDocument.
        Deletes the CoveoDocument to the Push API
        :arg p_DocumentId: str of the document to delete
        :arg updateStatus: bool (True), if the source status should be updated
        :arg orderingId: int, if not supplied a new one will be created
        :arg deleteChildren: bool (False), if children must be deleted
        """*/
        $this->logger->debug('RemoveSingleDocument');
        // Single Call

        // Update Source Status
        if ($updateStatus==true || $updateStatus==null){
            $this->UpdateSourceStatus(SourceStatusType::Rebuild);
        }

        // Delete document
        $this->DeleteDocument($p_DocumentId, $orderingId, $deleteChildren);

        // Update Source Status
        if ($updateStatus==true || $updateStatus==null){
            $this->UpdateSourceStatus(SourceStatusType::Idle);
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddUpdateDocumentsRequest(string $p_FileId) {
        /*"""
        AddUpdateDocumentsRequest.
        Sends the documents to the Push API, if previously uploaded to s3 the fileId is set
        :arg p_FileId: File Id retrieved from GetLargeFileContainer call
        """*/

        $this->logger->debug('AddUpdateDocumentsRequest '.$p_FileId);
        $params = array( Parameters::FILE_ID => $p_FileId);
        // make POST request to change status
        $result = $this->doPut( $this->GetUpdateDocumentsUrl(), $this->GetRequestHeaders(),null, $params);
        if ($result!=False) {
            return true;
        }
        else {
            return false;
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function UploadBatch(array $p_ToAdd, array $p_ToDelete){
        /*"""
        UploadBatch.
        Uploads the batch to S3 and calls the Push API to record the fileId
        :arg p_ToAdd: list of CoveoDocuments to add
        :arg p_ToDelete: list of CoveoDocumentToDelete to delete
        """*/

        $this->logger->debug('UploadBatch');
        if ($p_ToAdd==null && $p_ToDelete==null){
            $this->logger->debug("UploadBatch: p_ToAdd and p_ToDelete are empty");
            return;
        }
        $container = $this->GetLargeFileContainer();
        if ($container==null){
            $this->logger->debug("UploadBatch: S3 container is null");
            return;
        }
        $this->UploadDocuments($container->UploadUri, $p_ToAdd, $p_ToDelete);
        $this->AddUpdateDocumentsRequest($container->FileId);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function ProcessAndUploadBatch(array $p_Documents){
        /*"""
        ProcessAndUploadBatch.
        Will create batches of documents to push to S3 and to upload to the Push API
        :arg p_Documents: list of CoveoDocument/CoveoDocumentToDelete to add/delete
        """*/

        $this->logger->debug('ProcessAndUploadBatch');
        $currentBatchToDelete = array();
        $currentBatchToAddUpdate = array();

        $totalSize = 0;
        foreach ($p_Documents as $document){
            // Add 1 byte to account for the comma in the JSON array.
            // documentSize = len(json.dumps(document,default=lambda x: x.__dict__)) + 1
            $documentSize = strlen($document->ToJson()) + 1;

            $totalSize += $documentSize;
            $this->logger->debug("Doc: ".$document->DocumentId);
            $this->logger->debug("Currentsize: ".$totalSize . " vs max: ".($this->GetSizeMaxRequest()));

            if ($documentSize > $this->GetSizeMaxRequest()){
                $this->logger->debug("No document can be larger than " . $this->GetSizeMaxRequest()." bytes in size.");
                return;
            }

            if ($totalSize > $this->GetSizeMaxRequest() - (count($currentBatchToAddUpdate) + count($currentBatchToDelete))){
                $this->UploadBatch($currentBatchToAddUpdate, $currentBatchToDelete);
                $currentBatchToAddUpdate = array();
                $currentBatchToDelete = array();
                $totalSize = $documentSize;
            }

            if (is_a($document, 'Coveo\\SDK\\SDKPushPHP\\DocumentToDelete')){
                array_push($currentBatchToDelete,$document->cleanUp());//->ToJson());
            }
            else{
                // Validate each document
                list($valid, $error) = $document->Validate();
                if ($valid==False){
                    $this->logger->debug("PushDocument: " . $document->DocumentId . ", " . $error);
                    return;
                }
                else {
                 
                      array_push($currentBatchToAddUpdate,$document->cleanUp());//.ToJson());
                }
            }
        }

        $this->UploadBatch($currentBatchToAddUpdate, $currentBatchToDelete);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddDocuments(array $p_CoveoDocumentsToAdd, array $p_CoveoDocumentsToDelete, bool $p_UpdateStatus=null, bool $p_DeleteOlder=null){
        /*"""
        AddDocuments.
        Adds all documents in several batches to the Push API.
        :arg p_CoveoDocumentsToAdd: list of CoveoDocument to add
        :arg p_CoveoDocumentsToDelete: list of CoveoDocumentToDelete
        :arg p_UpdateStatus: bool (True), if the source status should be updated
        :arg p_DeleteOlder: bool (False), if older documents should be removed from the index after the new push
        """*/
        
        if ($p_UpdateStatus==null) {
            $p_UpdateStatus = True;
        }
        if ($p_DeleteOlder==null) {
            $p_DeleteOlder = False;
        }
        $this->logger->debug('AddDocuments');
        // Batch Call
        // First check
        $StartOrderingId = $this->CreateOrderingId();

        if ($p_CoveoDocumentsToAdd==null && $p_CoveoDocumentsToDelete==null) {
            $this->logger->debug("AddDocuments: p_CoveoDocumentsToAdd and p_CoveoDocumentsToDelete is empty");
            return;
        }

        // Update Source Status
        if ($p_UpdateStatus) {
            $this->UpdateSourceStatus(SourceStatusType::Rebuild);
        }

        // Push the Documents
        if (!empty($p_CoveoDocumentsToAdd)){
            $allDocuments = $p_CoveoDocumentsToAdd;
        }

        if (!empty($p_CoveoDocumentsToDelete)) {
            $allDocuments = array_merge($allDocuments,$p_CoveoDocumentsToDelete);
        }

        $this->ProcessAndUploadBatch($allDocuments);

        // Delete Older Documents
        if ($p_DeleteOlder) {
            $this->DeleteOlderThan($StartOrderingId);
        }

        // Update Source Status
        if ($p_UpdateStatus) {
            $this->UpdateSourceStatus(SourceStatusType::Idle);
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function Start(bool $p_UpdateStatus=null, bool $p_DeleteOlder=null){
        /*"""
        Start.
        Starts a batch Push call, will set the start ordering Id and will update the status of the source
        :arg p_UpdateStatus: bool (True), if the source status should be updated
        :arg p_DeleteOlder: bool (False), if older documents should be removed from the index after the new push
        """*/

        if ($p_UpdateStatus==null) {
            $p_UpdateStatus = True;
        }
        if ($p_DeleteOlder==null) {
            $p_DeleteOlder = False;
        }
        $this->logger->debug('Start');
        // Batch Call
        // First check
        $this->StartOrderingId = $this->CreateOrderingId();

        // Update Source Status
        if ($p_UpdateStatus){
            $this->UpdateSourceStatus(SourceStatusType::Rebuild);
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function Add($p_CoveoDocument){
        /*"""
        Add.
        Add a document to the batch call, if the buffer max is reached content is pushed
        :arg p_CoveoDocument: CoveoDocument of CoveoDocumentToDelete
        """*/

        $this->logger->debug('Add');

        if ($p_CoveoDocument==null){
            $this->logger->debug("Add: p_CoveoDocument is empty");
            return;
        }

        $documentSize = strlen($p_CoveoDocument->ToJson()) + 1;

        $this->totalSize += $documentSize;
        $this->logger->debug("Doc: ".$p_CoveoDocument->DocumentId);
        $this->logger->debug("Currentsize: ".$this->totalSize . " vs max: ".$this->GetSizeMaxRequest());

        if ($documentSize > $this->GetSizeMaxRequest()){
            $this->logger->debug("No document can be larger than " . $this->GetSizeMaxRequest()." bytes in size.");
            return;
        }

        if ($this->totalSize > $this->GetSizeMaxRequest() - (count($this->ToAdd) + count($this->ToDel))){
            $this->UploadBatch($this->ToAdd, $this->ToDel);
            $this->ToAdd = array();
            $this->ToDel = array();
            $this->totalSize = $documentSize;
        }

        if (is_a($p_CoveoDocument, 'Coveo\\SDK\\SDKPushPHP\\DocumentToDelete')){
            array_push($this->ToDel,$p_CoveoDocument->cleanUp());//->ToJson());
        }
        else{
            // Validate each document
            list($valid, $error) = $p_CoveoDocument->Validate();
            if (!$valid){
                $this->logger->debug("Add: ".$p_CoveoDocument->DocumentId.", ".$error);
                return;
            }
            else{
                array_push($this->ToAdd,$p_CoveoDocument->cleanUp());//->ToJson());
            }
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function End(bool $p_UpdateStatus=null, bool $p_DeleteOlder=null){
        /*"""
        End.
        Ends the batch call (when started with Start()). Will push the final batch, update the status and delete older documents
        :arg p_UpdateStatus: bool (True), if the source status should be updated
        :arg p_DeleteOlder: bool (False), if older documents should be removed from the index after the new push
        """*/
        if ($p_UpdateStatus==null) {
            $p_UpdateStatus = True;
        }
        if ($p_DeleteOlder==null) {
            $p_DeleteOlder = False;
        }
        $this->logger->debug('End');
        // Batch Call
        $this->UploadBatch($this->ToAdd, $this->ToDel);

        // Delete Older Documents
        $this->logger->debug('End Delete older:'.$p_DeleteOlder);
        if ($p_DeleteOlder) {
            $this->DeleteOlderThan($this->StartOrderingId);
        }

        $this->ToAdd = array();
        $this->ToDel = array();

        // Update Source Status
        if ($p_UpdateStatus){
            $this->UpdateSourceStatus(SourceStatusType::Idle);
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddSecurityProvider(string $p_SecurityProviderId, string $p_Type, array $p_CascadingTo, string $p_Endpoint=null){
        /*"""
        AddSecurityProvider.
        Add a single Permission Expansion (PermissionIdentityBody)
        :arg p_SecurityProviderId: Security Provider name and Id to use
        :arg p_Type: Type of provider, normally 'EXPANDED'
        :arg p_CascadingTo: dictionary
        :arg p_Endpoint: Constants.PlatformEndpoint
        """*/
        if ($p_Endpoint==null){
            $p_Endpoint = PlatformEndpoint::PROD_PLATFORM_API_URL;
        }
        $secProvider = new SecurityProvider();
        $secProviderReference = new SecurityProviderReference($this->SourceId, "SOURCE");
        $secProvider->referencedBy = array($secProviderReference);
        $secProvider->name = $p_SecurityProviderId;
        $secProvider->type = $p_Type;
        $secProvider->nodeRequired = False;
        $secProvider->cascadingSecurityProviders = $p_CascadingTo;

        $this->logger->debug('AddSecurityProvider');

        // make POST request to change status
        $provider = $this->cleanJSON($secProvider);
        $this->logger->debug("JSON: ".$provider);
        $result = $this->doPut( $this->GetSecurityProviderUrl($p_Endpoint,$p_SecurityProviderId), $this->GetRequestHeaders(),$provider);
        if ($result!=False) {
            return true;
        }
        else {
            return false;
        }
    }


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddPermissionExpansion(string $p_SecurityProviderId, PermissionIdentityExpansion $p_Identity, array $p_Members, array $p_Mappings, array $p_WellKnowns,int $orderingId=null){
        /*"""
        AddPermissionExpansion.
        Add a single Permission Expansion Call (PermissionIdentityBody)
        :arg p_SecurityProviderId: Security Provider to use
        :arg p_Identity: PermissionIdentityExpansion.
        :arg p_Members: list of PermissionIdentityExpansion.
        :arg p_Mappings: list of PermissionIdentityExpansion.
        :arg p_WellKnowns: list of PermissionIdentityExpansion.
        :arg orderingId: orderingId. (optional)
        """*/
        $this->logger->debug('AddPermissionExpansion');

        $permissionIdentityBody = new PermissionIdentityBody($p_Identity);
        $permissionIdentityBody->AddMembers($p_Members);
        $permissionIdentityBody->AddMappings($p_Mappings);
        $permissionIdentityBody->AddWellKnowns($p_WellKnowns);

        $params = array();

        if ($orderingId!=null){
            $params[Parameters::ORDERING_ID] = $orderingId;
        }

        $resourcePathFormat = PushApiPaths::PROVIDER_PERMISSIONS;
        if ($p_Mappings!=null) {
            $resourcePathFormat = PushApiPaths::PROVIDER_MAPPINGS;
        }

        $values = $this->createPath();
        $values['prov_id'] = $p_SecurityProviderId;
        $resourcePath = replacePath( $resourcePathFormat, $values);

        $identity = $this->cleanJSON($permissionIdentityBody);

        $this->logger->debug('JSON: '.$identity);

        $result = $this->doPut( $resourcePath, $this->GetRequestHeaders(),$identity,$params);
        if ($result!=False) {
            return true;
        }
        else {
            return false;
        }
    }
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    function StartExpansion(string $p_SecurityProviderId, bool $p_DeleteOlder=null){
        /*"""
        StartExpansion.
        Will start a Batch for Expansion/Permission updates.
        Using AddExpansionMember, AddExpansionMapping or AddExpansionDeleted operations are added.
        EndExpansion must be called at the end to write the Batch to the Push API.
        :arg p_SecurityProviderId: Security Provider to use
        :arg p_DeleteOlder: bool (False), if older documents should be removed from the index after the new push
        """*/

        $this->logger->debug('StartExpansion');
        // Batch Call
        // First check
        $this->StartOrderingId = $this->CreateOrderingId();
        $this->BatchPermissions = new BatchPermissions();
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddExpansionMember( PermissionIdentityExpansion $p_Identity, array $p_Members, array $p_Mappings, array $p_WellKnowns) {
        /*"""
        AddExpansionMember.
        For example: GROUP has 3 members.
        Add a single Permission Expansion (PermissionIdentityBody) to the Members
        :arg p_Identity: PermissionIdentityExpansion, must be the same as Identity in PermissionIdentity when pushing documents.
        :arg p_Members: list of PermissionIdentityExpansion.
        :arg p_Mappings: list of PermissionIdentityExpansion.
        :arg p_WellKnowns: list of PermissionIdentityExpansion.
        """*/
        $this->logger->debug('AddExpansionMember');
        $permissionIdentityBody = new PermissionIdentityBody($p_Identity);
        $permissionIdentityBody->AddMembers($p_Members);
        $permissionIdentityBody->AddMappings($p_Mappings);
        $permissionIdentityBody->AddWellKnowns($p_WellKnowns);
        $this->BatchPermissions->AddMembers($permissionIdentityBody);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddExpansionMapping(PermissionIdentityExpansion $p_Identity, array $p_Members, array $p_Mappings, array $p_WellKnowns) {
        /*"""
        AddExpansionMapping.
        For example: Identity WIM has 3 mappings: wim@coveo.com, w@coveo.com, ad\\w
        Add a single Permission Expansion (PermissionIdentityBody) to the Mappings
        :arg p_Identity: PermissionIdentityExpansion, must be the same as Identity in PermissionIdentity when pushing documents.
        :arg p_Members: list of PermissionIdentityExpansion.
        :arg p_Mappings: list of PermissionIdentityExpansion.
        :arg p_WellKnowns: list of PermissionIdentityExpansion.
        """*/
        $this->logger->debug('AddExpansionMapping');
        $permissionIdentityBody = new PermissionIdentityBody($p_Identity);
        $permissionIdentityBody->AddMembers($p_Members);
        $permissionIdentityBody->AddMappings($p_Mappings);
        $permissionIdentityBody->AddWellKnowns($p_WellKnowns);
        $this->BatchPermissions->AddMappings($permissionIdentityBody);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddExpansionDeleted(PermissionIdentityExpansion $p_Identity, array $p_Members, array $p_Mappings, array $p_WellKnowns) {
        /*"""
        AddExpansionDeleted.
        Add a single Permission Expansion (PermissionIdentityBody) to the Deleted, will be deleted from the security cache
        :arg p_Identity: PermissionIdentityExpansion, must be the same as Identity in PermissionIdentity when pushing documents.
        :arg p_Members: list of PermissionIdentityExpansion.
        :arg p_Mappings: list of PermissionIdentityExpansion.
        :arg p_WellKnowns: list of PermissionIdentityExpansion.
        """*/
        $this->logger->debug('AddExpansionDeleted');
        $permissionIdentityBody = new PermissionIdentityBody($p_Identity);
        $permissionIdentityBody->AddMembers($p_Members);
        $permissionIdentityBody->AddMappings($p_Mappings);
        $permissionIdentityBody->AddWellKnowns($p_WellKnowns);
        $this->BatchPermissions->AddDeletes($permissionIdentityBody);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function EndExpansion(string $p_SecurityProviderId, bool $p_DeleteOlder=null){
        /*"""
        EndExpansion.
        Will write the last batch of security updates to the push api
        :arg p_SecurityProviderId: Security Provider to use
        :arg p_DeleteOlder: bool (False), if older documents should be removed from the index after the new push
        """*/
        if ($p_DeleteOlder==null) {
            $p_DeleteOlder = False;
        }
        $this->logger->debug('EndExpansion');
        $container = $this->GetLargeFileContainer();
        if ($container==null){
            $this->logger->debug("UploadBatch: S3 container is null");
            return;
        }

        $this->UploadPermissions($container->UploadUri);
        $params = array(Parameters::FILE_ID=> $container->FileId);

        $values = $this->createPath();
        $values['prov_id'] = $p_SecurityProviderId;
        $resourcePath = replacePath( PushApiPaths::PROVIDER_PERMISSIONS_BATCH, $values);

        $result = $this->doPut( $resourcePath, $this->GetRequestHeaders(),null,$params);

        

        if ($p_DeleteOlder) {
            $this->DeletePermissionsOlderThan($p_SecurityProviderId, $this->StartOrderingId);
        }

        if ($result!=False) {
            return true;
        }
        else {
            return false;
        }
    }
    
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function RemovePermissionIdentity(string $p_SecurityProviderId, PermissionIdentityExpansion $p_PermissionIdentity) {
        /*"""
        RemovePermissionIdentity.
        Remove a single Permission Mapping
        :arg p_SecurityProviderId: Security Provider to use
        :arg p_PermissionIdentity: PermissionIdentityExpansion, permissionIdentity to remove
        """*/
        $this->logger->debug('RemovePermissionIdentity');
        $permissionIdentityBody = new PermissionIdentityBody($p_PermissionIdentity);

        $values = $this->createPath();
        $values['prov_id'] = $p_SecurityProviderId;
        $resourcePath = replacePath( PushApiPaths::PROVIDER_PERMISSIONS, $values);
        $identity = $this->cleanJSON($permissionIdentityBody);

        $this->logger->debug("JSON: " . $identity);


        $result = $this->doDelete( $resourcePath, $this->GetRequestHeaders(),null, $identity);

        if ($result!=False) {
            return true;
        }
        else {
            return false;
        }

    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function DeletePermissionsOlderThan(string $p_SecurityProviderId, int $orderingId=null){
        /*"""
        DeletePermissionOlderThan.
        Deletes permissions older than orderingId
        :arg p_SecurityProviderId: Security Provider to use
        :arg orderingId: int, the OrderingId to use
        """*/
        $this->logger->debug('DeletePermissionsOlderThan');

        if ($orderingId <= 0){
            $this->logger->debug("DeletePermissionsOlderThan: orderingId must be a positive 64 bit integer.");
            return;
        }

        $params = array(Parameters::ORDERING_ID => $orderingId);

        $values = $this->createPath();
        $values['prov_id'] = $p_SecurityProviderId;
        $resourcePath = replacePath( PushApiPaths::PROVIDER_PERMISSIONS_DELETE, $values);



        $result = $this->doDelete( $resourcePath, $this->GetRequestHeaders(),$params);

        if ($result!=False) {
            return true;
        }
        else {
            return false;
        }


    }
}
