<?php
// -------------------------------------------------------------------------------------
// CoveoConstants
// -------------------------------------------------------------------------------------
// Contains the Constants used by the SDK
// -------------------------------------------------------------------------------------

namespace Coveo\Search\SDK\SDKPushPHP;

use Coveo\Search\SDK\SDKPushPHP\Enum as Enum;
use Coveo\Search\Api\Service\LoggerInterface;
use \Exception;

//---------------------------------------------------------------------------------
class Constants{
    //"""Constants used within the Push Classes """
    // The default request timeout in seconds.
    const DEFAULT_REQUEST_TIMEOUT_IN_SECONDS = 100;

    // The default date format used by the Push API.
    const DATE_FORMAT_STRING = "yyyy-MM-dd HH:mm:ss";

    // The date format used by the Activities service of the Platform API.
    const DATE_WITH_MILLISECONDS_FORMAT_STRING = "yyyy-MM-ddTHH:mm:ss.fffZ";

    // The name of the default 'Email' security provider provisioned with each organization.
    const EMAIL_SECURITY_PROVIDER_NAME = "Email Security Provider";

    // Max size (in bytes) of a document after being compressed-encoded.
    const COMPRESSED_DATA_MAX_SIZE_IN_BYTES = 5*1024*1024;

    // Max size (in bytes) of a request.
    // Limit in the Push API consumer is 256MB. --> was to big, we use 250 to be safe
    // 32 bytes is removed from it to account for the JSON body structure.
    const MAXIMUM_REQUEST_SIZE_IN_BYTES = 250*1024*1024;

    // Reserved key names (case-insensitive) used in the Push API.
    const s_DocumentReservedKeys = array("author","clickableUri", "compressedBinaryData","compressedBinaryDataFileId","compressionType","data","date","documentId", "fileExtension", "parentId", "permissions", "orderingId" );
}
// ---------------------------------------------------------------------------------
class SourceStatusType extends Enum{
    const Rebuild = "REBUILD";
    const Refresh = "REFRESH";
    const Incremental = "INCREMENTAL";
    const Idle = "IDLE";
}
// ---------------------------------------------------------------------------------
class PermissionIdentityType extends Enum{
    // Represents a standard, or undefined identity.
    const Unknown = "UNKNOWN";

    // Represents a 'User' identity.
    const User = "USER";

    // Represents a 'Group' identity.
    const Group = "GROUP";

    // Represents a 'VirtualGroup' identity.
    const VirtualGroup = "VIRTUAL_GROUP";
}

// ---------------------------------------------------------------------------------
class CompressionType extends Enum {
    const UNCOMPRESSED = "UNCOMPRESSED";
    const DEFLATE = "DEFLATE";
    const GZIP = "GZIP";
    const LZMA = "LZMA";
    const ZLIB = "ZLIB";
}
// ---------------------------------------------------------------------------------
class Retry{
    // The default number of retries when a request fails on a retryable error.
    const DEFAULT_NUMBER_OF_RETRIES = 5;

    // The default initial waiting time in milliseconds when a retry is performed.
    const DEFAULT_INITIAL_WAITING_TIME_IN_MS = 2000;

    // The maximum waiting time interval in milliseconds to add for each retry.
    const DEFAULT_MAX_INTERVAL_TIME_TO_ADD_IN_MS = 2000;
}
// ---------------------------------------------------------------------------------
class ErrorCodes{
    const Codes = array(
        "429" => "Too many requests. Slow down your pushes! Are you using Batch Calls?",
    "413" => "Request too large. The document is too large to be processed. It should be under 5 mb.",
    "412" => "Invalid or missing parameter - invalid source id",
    "403" => "Access Denied. Validate that your API Key has the proper access and that your Org and Source Id are properly specified",
    "401" => "Unauthorized or invalid token. Ensure your API key has the appropriate permissions.",
    "400" => "Organization is Paused. Reactivate it OR Invalid JSON",
    "504" => "Timeout"
    );
}
// ---------------------------------------------------------------------------------
class PlatformEndpoint{
    const PROD_PLATFORM_API_URL = "https://platform.cloud.coveo.com";
    const HIPAA_PLATFORM_API_URL = "https://platformhipaa.cloud.com";
    const QA_PLATFORM_API_URL = "https://platformqa.cloud.coveo.com";
    const DEV_PLATFORM_API_URL = "https://platformdev.cloud.coveo.com";
}
// ---------------------------------------------------------------------------------
class PlatformPaths{
    const CREATE_PROVIDER = "{endpoint}/rest/organizations/{org_id}/securityproviders/{prov_id}";
}
// ---------------------------------------------------------------------------------
class PushApiEndpoint{
    const PROD_PUSH_API_URL = "https://api.cloud.coveo.com/push/v1";
    const HIPAA_PUSH_API_URL = "https://api-hipaa.cloud.coveo.com/push/v1";
    const QA_PUSH_API_URL = "https://api-qa.cloud.coveo.com/push/v1";
    const DEV_PUSH_API_URL = "https://api-dev.cloud.coveo.com/push/v1";
}
// ---------------------------------------------------------------------------------
class PushApiPaths{
    const SOURCE_ACTIVITY_STATUS = "{endpoint}/organizations/{org_id}/sources/{src_id}/status";
    const SOURCE_DOCUMENTS = "{endpoint}/organizations/{org_id}/sources/{src_id}/documents";
    const SOURCE_DOCUMENTS_BATCH = "{endpoint}/organizations/{org_id}/sources/{src_id}/documents/batch";
    const SOURCE_DOCUMENTS_DELETE = "{endpoint}/organizations/{org_id}/sources/{src_id}/documents/olderthan";
    const DOCUMENT_GET_CONTAINER = "{endpoint}/organizations/{org_id}/files";
    const PROVIDER_PERMISSIONS = "{endpoint}/organizations/{org_id}/providers/{prov_id}/permissions";
    const PROVIDER_PERMISSIONS_DELETE = "{endpoint}/organizations/{org_id}/providers/{prov_id}/permissions/olderthan";
    const PROVIDER_PERMISSIONS_BATCH = "{endpoint}/organizations/{org_id}/providers/{prov_id}/permissions/batch";
    const PROVIDER_MAPPINGS = "{endpoint}/organizations/{org_id}/providers/{prov_id}/mappings";
}

function replacePath(string $path, array $values) {
    $newpath=$path;
    $origin = array("{endpoint}", "{org_id}", "{src_id}","{prov_id}");
    $to   = array($values['endpoint'],$values['org_id'],$values['src_id'],$values['prov_id']);

    $newpath = str_replace($origin, $to, $newpath);
    return $newpath;
}
// ---------------------------------------------------------------------------------
class Parameters{
    const STATUS_TYPE = "statusType";
    const FILE_ID = "fileId";
    const ORDERING_ID = "orderingId";
    const DOCUMENT_ID = "documentId";
    const QUEUE_DELAY = "queueDelay";
    const DELETE_CHILDREN = "deleteChildren";
    const COMPRESSION_TYPE = "compressionType";
}
// ---------------------------------------------------------------------------------
class HttpHeaders{
    const AMAZON_S3_SERVER_SIDE_ENCRYPTION_NAME = "x-amz-server-side-encryption";
    const AMAZON_S3_SERVER_SIDE_ENCRYPTION_VALUE = "AES256";
}

