<?php
// -------------------------------------------------------------------------------------
// CoveoDocument
// -------------------------------------------------------------------------------------
// Contains the CoveoDocument class
//   A CoveoDocument will be pushed to the push source
// -------------------------------------------------------------------------------------
namespace Coveo\Search\SDK\SDKPushPHP;
use \DateTime;
use Coveo\Search\Api\Service\LoggerInterface;
// ---------------------------------------------------------------------------------




class BatchDocument{
    /*"""
    class BatchDocument.
    Class to hold the Batch Document.
    """*/
    public $AddOrUpdate = array();
    public $Delete = array();
}
// ---------------------------------------------------------------------------------


class DocumentToDelete{
    /*"""
    class DocumentToDelete.
    Class to hold the Document To Delete.
    It should consist of the DocumentId (URL) only."""*/
    // The unique document identifier for the source, must be the document URI.
    public $DocumentId = '';
    public $Title = '';

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function __construct(string $p_DocumentId) {
        $this->DocumentId = $p_DocumentId;
        $this->Title = $p_DocumentId;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function ToJson(){
        /*"""ToJson, returns JSON for push.
        Puts all metadata and other fields into clean"""*/
        // Check if empty

        $all=array();
        $all["DocumentId"] = $this->DocumentId;
        return json_encode($all);
    }
}

// ---------------------------------------------------------------------------------


class Document{
    /*"""
    class Document.
    Class to hold the Document To Push.
    Mandatory properties: DocumentId (URL) and Title."""*/
    public $Data = '';
    public $Date = '';
    public $DocumentId = '';
    public $permanentid = '';
    public $Title = '';
    public $ModifiedDate = '';
    public $CompressedBinaryData = '';
    public $CompressedBinaryDataFileId = '';
    public $CompressionType = '';
    public $FileExtension = '';
    public $ParentId = '';
    public $ClickableUri = '';
    public $Author = '';
    public $Permissions = array();
    public $MetaData = array();
    /**
     * @var LoggerInterface
     */
    protected $logger;

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function __construct(string $p_DocumentId, LoggerInterface $logger){
        /*"""
        class Document constructor.
        :arg p_DocumentId: Document Id, valid URL
        """*/
        $this->DocumentId = $p_DocumentId;
        $this->permanentid = $this->hashdoc($p_DocumentId);
        $this->Permissions = array();
        $this->MetaData = array();
        $this->Data = '';
        $this->Date = '';
        $this->Title = '';
        $this->ModifiedDate = '';
        $this->CompressedBinaryData = '';
        $this->CompressedBinaryDataFileId = '';
        $this->CompressionType = '';
        $this->FileExtension = '';
        $this->ParentId = '';
        $this->ClickableUri = '';
        $this->Author = '';
        $this->logger = $logger;

    }

        
    function hashdoc($documentId){
      $hash_object = hash('sha256',utf8_encode($documentId));
      return $hash_object;
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
    // ---------------------------------------------------------------------------------


    function Validate(){
      /*"""
      Validate.
      Validates if all properties on the CoveoDocument are properly set.
      Returns True/False, Error
      """*/
      $result = True;
      $error = array();
      if ($this->permanentid == '') {
          array_push($error,'permanentid is empty');
          $result = False;
      }
      if ($this->DocumentId == '') {
          array_push($error,'DocumentId is empty');
          $result = False;
      }
      // data or CompressedBinaryData should be set, not both
      if ($this->Data && $this->CompressedBinaryData) {
          array_push($error,'Both Data and CompressedBinaryData are set');
          $result = False;
      }
      // Validate documentId, should be a valid url
      try{
          $parsed_url = parse_url($this->DocumentId);

          if (!$parsed_url["scheme"]) {
              array_push($error,'DocumentId is not a valid URL format [missing scheme]: ' . $this->DocumentId);
              $result = False;
          }

          if (!$parsed_url["path"]){
              array_push($error,'DocumentId is not a valid URL format [missing path]: ' . $this->DocumentId);
              $result = False;
          }
      }
      catch(Exception $e) {
          array_push($error,'DocumentId is not a valid URL format:' . $this->DocumentId);
          $result = False;
      }
      $this->logger->info('[Validate Document] '.implode($error,' | '));
      return array($result, implode($error,' | '));
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function ToJson(){
        /*"""
        ToJson, returns JSON for push.
        Puts all metadata and other fields into a clean JSON object"""*/
        // Check if empty

        $attributes = array(
            'DocumentId', 'permanentid', 'Title', 'ClickableUri',
            'Data', 'CompressedBinaryData', 'CompressedBinaryDataFileId', 'CompressionType',
            'Date', 'ModifiedDate',
            'FileExtension',
            'ParentId',
            'Author', 'Permissions'
        );

        $all = array();
        foreach ($attributes as &$attr){
          //echo $attr;
            if (property_exists("Coveo\Search\\SDK\\SDKPushPHP\\Document", $attr)){
              //echo $attr;
              if (is_array ( $this->{$attr})) {
                if (count($this->{$attr})>0) {
                  $all[$attr] = $this->{$attr};
                }
              } else {
                if ($this->{$attr}!="") {
                  $all[$attr] = $this->{$attr};
                }
              }
            }
        }
        foreach ($this->MetaData as $key => $value){
            $all[$key] = $value;
        }
        //echo json_encode($all);
        return json_encode($all);
    }

    function cleanUp(){
      /*"""
      ToJson, returns JSON for push.
      Puts all metadata and other fields into a clean JSON object"""*/
      // Check if empty

      $attributes = array(
          'DocumentId', 'permanentid', 'Title', 'ClickableUri',
          'Data', 'CompressedBinaryData', 'CompressedBinaryDataFileId', 'CompressionType',
          'Date', 'ModifiedDate',
          'FileExtension',
          'ParentId',
          'Author', 'Permissions'
      );

      $all = array();
      foreach ($attributes as &$attr){
        //echo $attr;
          if (property_exists("Coveo\Search\\SDK\\SDKPushPHP\\Document", $attr)){
            //echo $attr;
            if (is_array ( $this->{$attr})) {
              if (count($this->{$attr})>0) {
                $all[$attr] = $this->{$attr};
              }
            } else {
              if ($this->{$attr}!="") {
                $all[$attr] = $this->{$attr};
              }
            }
          }
      }
      foreach ($this->MetaData as $key => $value){
          $all[$key] = $value;
      }
      //echo json_encode($all);
      $source = json_encode($all);
  //Debug($source);
  $result = preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', $source);
  $result = preg_replace('/,\s*"[^"]+":\[\]|"[^"]+":\[\],?/', '', $source);
  //Debug($result);
  //return $result;
      return json_decode($result);
  }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function SetData(string $p_Data){
        /*"""
        SetData.
        Sets the Data (plain text) property.
        :arg p_Data: str, sets the Data (Plain Text)
        """*/

        //Debug('SetData');
        // Check if empty
        if ($p_Data == ''){
          $this->logger->error('[Setdata] : value not set');
            return;
        }

        $this->Data = $p_Data;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function SetDate( DateTime $p_Date){
        /*"""
        SetDate.
        Sets the date property.
        :arg p_Date: datetime, set the date
        """*/

        // if string, parse it into a datetime
        if (is_string($p_Date)){
            $p_Date = date(DATE_ISO8601, strtotime($p_Date));
        }

        // Check we have a datetime object
        if (!is_a($p_Date, 'DateTime')){
          $this->logger->error("SetDate: invalid datetime object");
            return;
        }

        $this->Date = $p_Date->format(DateTime::ATOM);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function SetModifiedDate(DateTime $p_Date){
        /*"""
        SetModifiedDate.
        Sets the ModifiedDate property.
        :arg p_Date: datetime, set the ModifiedDate date
        """*/
        // if string, parse it into a datetime
        if (is_string($p_Date)){
            $p_Date = date(DATE_ISO8601, strtotime($p_Date));
        }

        // Check we have a datetime object
        if (!is_a($p_Date, 'DateTime')){
          $this->logger->error("SetModifiedDate: invalid datetime object");
            return;
        }

        $this->ModifiedDate = $p_Date->format(DateTime::ATOM);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function SetCompressedEncodedData(string $p_CompressedEncodedData, $p_CompressionType=null){
        /*"""
        SetCompressedEncodedData.
        Sets the CompressedBinaryData property.
        Make sure to set the proper CompressionType and Base64 encode the CompressedEncodedData.
        :arg p_CompressedEncodedData: str, Encoded Data (base64 ecoded)
        :arg p_CompressionType: CoveoConstants.Constants.CompressionType (def: ZLIB), CompressionType to Use
        """*/
        if ($p_CompressionType==null){
            $this->p_CompressionType = CompressionType::ZLIB;
        }

        $this->logger->debug('SetCompressedEncodedData');
        // Check if empty
        if ($p_CompressedEncodedData == ''){
          $this->logger->error("SetCompressedEncodedData: value not set");
            return;
        }

        // Check if base64 encoded
        if (!$this->isBase64($p_CompressedEncodedData)){
          $this->logger->error("SetCompressedEncodedData: value must be base64 encoded.");
            return;
        }

        $this->CompressedBinaryData = $p_CompressedEncodedData;
        $this->CompressedBinaryDataFileId = '';
        $this->CompressionType = $p_CompressionType;
    }
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function SetContentAndZLibCompress(string $p_Content) {
        /*"""
        SetContentAndCompress.
        Sets the CompressedBinaryData property, it will ZLIB compress the string and base64 encode it
        :arg p_Content: str, string
        """*/

        $this->logger->debug('SetContentAndZLibCompress');
        // Check if empty
        if ($p_Content == ''){
          $this->logger->error("SetContentAndZLibCompress: value not set");
            return;
        }


        $compresseddata = gzcompress($p_Content,9);
        $encodeddata = base64_encode($compresseddata);

        $this->CompressedBinaryData = $encodeddata;
        $this->CompressedBinaryDataFileId = '';
        $this->CompressionType = CompressionType::ZLIB;
    }
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function GetFileAndCompress(string $p_FilePath){
        /*"""
        GetFileAndCompress.
        Gets the file, compresses it (ZLIB), base64 encode it, set the filetype
        :arg p_FilePath: str, valid file
        """*/
        $this->logger->debug('GetFileAndCompress');
        $this->logger->debug($p_FilePath);
        // Check if empty
        if ($p_FilePath == ''){
          $this->logger->error("GetFileAndCompress: value not set");
            return;
        }

        // Check if file exists
        if (!file_exists($p_FilePath)){
          $this->logger->error("GetFileAndCompress: file does not exists ".$p_FilePath);
            return;
        }

        $filecontent = file_get_contents($p_FilePath);
        $compresseddata = gzcompress($filecontent,9);
        $encodeddata = base64_encode($compresseddata);

        $this->CompressedBinaryData = $encodeddata;
        $this->CompressedBinaryDataFileId = '';
        $this->CompressionType = CompressionType::ZLIB;
        $this->FileExtension = pathinfo($p_FilePath, PATHINFO_EXTENSION);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function SetCompressedDataFileId(string $p_CompressedDataFileId){
        /*"""
        SetCompressedDataFileId.
        Sets the CompressedBinaryDataFileId property.
        :arg p_CompressedDataFileId: str, the fileId retrieved by the GetLargeFileContainer call
        """*/
        $this->logger->debug('SetCompressedDataFileId');
        $this->logger->debug($p_CompressedDataFileId);
        // Check if empty
        if ($p_CompressedDataFileId == ''){
          $this->logger->error("SetCompressedDataFileId: value not set");
            return;
        }

        $this->CompressedBinaryData = '';
        $this->Data = '';
        $this->CompressedBinaryDataFileId = $p_CompressedDataFileId;
    }
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddMetadata(string $p_Key, $p_Value){
        /*"""
        AddMetadata.
        Sets the metadata.
        :arg p_Key: str, the key value to set
        :arg p_Value: object, the value or object to set (str or list)
        """*/
        $this->logger->debug('AddMetadata');
        //Debug($p_Key . ": " . mb_convert_encoding(utf8_encode($p_Value), "UTF-8", "ASCII"));
        // Check if empty
        if ($p_Key == ''){
          $this->logger->error("AddMetadata: key not set");
            return;
        }

        // Check if in reserved keys
        $lower = strtolower($p_Key);
        if (array_key_exists($lower, Constants::s_DocumentReservedKeys)) {
          $this->logger->error("AddMetadata: " . $p_Key . " is a reserved field and cannot be set as metadata.");
            return;
        }

        // Check if empty
        if ($p_Value == '' || $p_Value == null){
          $this->logger->warn("AddMetadata: value not set");
            return;
        } else {
            $this->MetaData[$lower] = $p_Value;
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function SetAllowedAndDeniedPermissions(array $p_AllowedPermissions, array $p_DeniedPermissions, bool $p_AllowAnonymous=null){
        /*"""
        SetAllowedAndDeniedPermissions.
        Sets the permissions on the document.
        :arg p_AllowedPermissions: list of PermissionIdentities which have access
        :arg p_DeniedPermissions: list of PermissionIdentities which do NOT have access
        :arg p_AllowAnonymous: (def: False) if Anonymous access is allowed
        """*/
        if ($p_AllowAnonymous==null) {
            $p_AllowAnonymous = False;
        }

        $this->logger->debug('SetAllowedAndDeniedPermissions');
        // Check if empty
        if ($p_AllowedPermissions == null){
          $this->logger->error("SetAllowedAndDeniedPermissions: AllowedPermissions not set");
            return;
        }
        

        $simplePermissionLevel = new DocumentPermissionLevel('Level1');

        $simplePermissionSet = new DocumentPermissionSet('Set1');
        $simplePermissionSet->AddAllowedPermissions($p_AllowedPermissions);
        $simplePermissionSet->AddDeniedPermissions($p_DeniedPermissions);
        $simplePermissionSet->AllowAnonymous = $p_AllowAnonymous;

        $simplePermissionLevel->AddPermissionSet($simplePermissionSet);

        array_push($this->Permissions,$simplePermissionLevel);
    }
}
