<?php
// -------------------------------------------------------------------------------------
// CoveoPermissions
// -------------------------------------------------------------------------------------
// Contains the Permissions which are used inside the CoveoDocument
//   PermissionSets, PermisionLevels and Permissions
// -------------------------------------------------------------------------------------
namespace Coveo\Search\SDK\SDKPushPHP;

use Coveo\Search\SDK\SDKPushPHP\Enum;
use Coveo\Search\SDK\SDKPushPHP\Constants;
use Coveo\Search\Api\Service\LoggerInterface;


class PermissionIdentity{
    /*"""
    class PermissionIdentity.
    Class to hold the Permission Identity.
    identityType (User, Group, Virtual Group ==> PermissionIdentityType),
    identity (for example: *@* or peter@coveo.com),
    securityProvider (for example: Confluence Provider).
    """*/
    // The identityType (User, Group or Virtual Group).
    // PermissionIdentityType
    public $identityType = '';

    // The associated identity provider identifier.
    // By default, if no securityProvider is specified, the identity will be associated the default
    // securityProvider defined in the configuration.
    public $securityProvider = '';

    // The identity provided by the identity provider to identify the permission identity.
    public $identity = '';

    // The additional information is a collection of key value pairs that
    // can be used to uniquely identify the permission identity.
    public $AdditionalInfo = array();

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function __construct( string $p_IdentityType, string $p_SecurityProvider, string $p_Identity,array $p_AdditionalInfo=null){
        /*"""
        class PermissionIdentity constructor.
        :arg p_IdentityType: PermissionIdentityType.
        :arg p_SecurityProvider: Security Provider name
        :arg p_Identity: Identity to add
        :arg p_AdditionalInfo: AdditionalInfo dict {} to add
        """*/
        if ($p_AdditionalInfo==null) {
            $p_AdditionalInfo = array();
        }
        $this->identity = $p_Identity;
        $this->securityProvider = $p_SecurityProvider;
        $this->identityType = $p_IdentityType;
        $this->AdditionalInfo = $p_AdditionalInfo;
    }
}


// ---------------------------------------------------------------------------------
class PermissionIdentityExpansion{
    /*"""
    class PermissionIdentityExpansion.
    Class to hold the Permission Identity for expansion.
    identityType (User, Group, Virtual Group ==> PermissionIdentityType),
    identity (for example: *@* or peter@coveo.com),
    securityProvider (for example: Confluence Provider).
    """*/
    // The identityType/Type (User, Group or Virtual Group).
    // PermissionIdentityType
    public $type = '';

    // The associated identity provider identifier.
    // By default, if no securityProvider is specified, the identity will be associated the default
    // securityProvider/Provider defined in the configuration.
    public $provider = '';

    // The identity/name provided by the identity provider to identify the permission identity.
    public $name = '';

    // The additional information is a collection of key value pairs that
    // can be used to uniquely identify the permission identity.
    public $additionalInfo = array();

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function __construct( string $p_IdentityType, string $p_SecurityProvider, string $p_Identity,array $p_AdditionalInfo=null){
        /*"""
        class PermissionIdentity constructor.
        :arg p_IdentityType: PermissionIdentityType.
        :arg p_SecurityProvider: Security Provider name
        :arg p_Identity: Identity to add
        :arg p_AdditionalInfo: AdditionalInfo dict {} to add
        """*/
        if ($p_AdditionalInfo==null) {
            $p_AdditionalInfo = array();
        }
        $this->name = $p_Identity;
        $this->provider = $p_SecurityProvider;
        $this->type = $p_IdentityType;
        $this->additionalInfo = $p_AdditionalInfo;
    }
}

// ---------------------------------------------------------------------------------
class DocumentPermissionSet{
    /*"""
    class DocumentPermissionSet.
    Class to hold one Permission Set.
    """*/
    // The name of the permission set.
    public $Name = '';

    // Whether to allow anonymous access to the document or not.
    public $AllowAnonymous = False;

    // The allowed permissions. List of PermissionIdentity
    public $AllowedPermissions = array();

    // The denied permissions. List of PermissionIdentity
    public $DeniedPermissions = array();

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Default constructor used by the deserialization.
    function __construct( string $p_Name){
        $this->Name = $p_Name;
        $this->AllowAnonymous = False;
        $this->AllowedPermissions = array();
        $this->DeniedPermissions = array();
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddAllowedPermissions( $p_PermissionIdentities) {
        /*"""
        AddAllowedPermissions.
        Add a list of PermissionIdentities to the AllowedPermissions
        :arg p_PermissionIdentities: list of PermissionIdentity.
        """*/
        //Debug('AddAllowedPermissions');
        // Check if correct
        if ($p_PermissionIdentities==null || empty($p_PermissionIdentities)) {
            return;
        }

        if (!is_array($p_PermissionIdentities)){
            $p_PermissionIdentities = array($p_PermissionIdentities);
        }
        if (!is_a($p_PermissionIdentities[0], 'Coveo\\SDK\\SDKPushPHP\\PermissionIdentity')){
            //Error( "AddAllowedPermissions: value is not of type PermissionIdentity");
            return;
        }


        $this->AllowedPermissions = array_merge( $this->AllowedPermissions, $p_PermissionIdentities);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddDeniedPermissions( $p_PermissionIdentities){
        /*"""
        AddDeniedPermissions.
        Add a list of PermissionIdentities to the DeniedPermissions
        :arg p_PermissionIdentities: list of PermissionIdentity.
        """*/
        //Debug('AddDeniedPermissions');
        // Check if correct
        if ($p_PermissionIdentities==null || empty($p_PermissionIdentities)) {
            return;
        }

        if (!is_array($p_PermissionIdentities)){
            $p_PermissionIdentities = array($p_PermissionIdentities);
        }
        if (!is_a($p_PermissionIdentities[0], 'Coveo\\SDK\\SDKPushPHP\\PermissionIdentity')){
            //Error( "AddDeniedPermissions: value is not of type PermissionIdentity");
            return;
        }


        $this->DeniedPermissions = array_merge( $this->DeniedPermissions, $p_PermissionIdentities);
    }
}


// ---------------------------------------------------------------------------------
class DocumentPermissionLevel{
    /*"""
    class DocumentPermissionLevel.
    Class to hold one Permission Level.
    """*/
    // The name of the permission level.
    public $Name = '';

    // The permission sets. Points to DocumentPermissionSet
    public $PermissionSets = array();

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Default constructor used by the deserialization.
    function __construct( string $p_Name){
        $this->Name = $p_Name;
        $this->PermissionSets = array();
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddPermissionSet(DocumentPermissionSet $p_DocumentPermissionSet){
        /*"""
        AddPermissionSet.
        Add a DocumentPermissionSet to the current Level.
        :arg p_DocumentPermissionSet: DocumentPermissionSet.
        """*/
        //Debug('AddPermissionSet');
        // Check if correct
        if (!is_a($p_DocumentPermissionSet, 'Coveo\\SDK\\SDKPushPHP\\DocumentPermissionSet')){
            //Error( "AddPermissionSet: value is not of type DocumentPermissionSet");
            return;
        }

        array_push( $this->PermissionSets, $p_DocumentPermissionSet);
    }
}


// ---------------------------------------------------------------------------------
class PermissionIdentityBody{
    /*"""
    class PermissionIdentityBody.
    Class to hold all associated Permission information for one Identity.
    """*/
    // The identity.
    // The identity is represented by a Name, a Type (User, Group or Virtual Group) and its Addtionnal Info).
    // PermissionIdentity
    public $identity = '';

    // The mappings of a user.
    // Link different user identities in different systems that represent the same person.
    // For example:
    //     - corp\myuser (Active Directory)
    //     - myuser@myenterprise.com (Email)
    // List of PermissionIdentityExpansion
    public $mappings = array();

    // The members of a group or a virtual group (membership).
    // List of PermissionIdentityExpansion
    public $members = array();

    // The well-knowns.
    // Well-known is a group that identifies generic users or generic groups.
    // For example, in the Active Directory:
    // - Everyone: automatically includes everyone who uses the computer, even anonymous guests.
    // - Anonymous: automatically includes all users that have logged on anonymously.
    // List of PermissionIdentityExpansion
    public $wellKnowns = array();

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Default constructor used by the deserialization.
    function __construct(PermissionIdentityExpansion $p_Identity){
        /*"""
        Constructor PermissionIdentityBody.
        :arg p_Identity: Identity name.
        """*/
        if (!is_a($p_Identity, 'Coveo\\SDK\\SDKPushPHP\\PermissionIdentityExpansion')){
            //Error("PermissionIdentityBody constructor: value is not of type PermissionIdentityExpansion");
            return;
        }

        $this->identity = $p_Identity;
        $this->mappings = array();
        $this->members = array();
        $this->wellKnowns = array();
    }

    function __add( &$attr,  $p_PermissionIdentities){
        /*"""
        Add.
        Add a PermissionIdentity to the self[attr]
        :arg attr: name of array to add the identities to (mappings, members, wellKnowns).
        :arg p_PermissionIdentity: PermissionIdentityExpansion.
        """*/
        // Check if correct
        if ($p_PermissionIdentities==null || empty($p_PermissionIdentities)) {
            return;
        }

        if (!is_array($p_PermissionIdentities)){
            $p_PermissionIdentities = array($p_PermissionIdentities);
        }
       // $type = ($p_PermissionIdentities[0] instanceof PermissionIdentityExpansion);
        //Debug(json_encode($p_PermissionIdentities));
        if (!is_a($p_PermissionIdentities[0], 'Coveo\\SDK\\SDKPushPHP\\PermissionIdentityExpansion')){
            //Error( "_add: value is not of type PermissionIdentityExpansion");
            return;
        }


        $attr = array_merge( $attr, $p_PermissionIdentities);
    }


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddMembers( $p_PermissionIdentities){
      //Debug('AddMembers');
        $this->__add($this->members, $p_PermissionIdentities);
    }

    function AddMappings( $p_PermissionIdentities){
      //Debug('AddMappings');
        $this->__add($this->mappings, $p_PermissionIdentities);
    }

    function AddWellKnowns( $p_PermissionIdentities){
      //Debug('AddWellKnowns');
        $this->__add($this->wellKnowns, $p_PermissionIdentities);
    }
}


// ---------------------------------------------------------------------------------
class BatchPermissions{
    /*"""
    class BatchPermissions.
    Class to hold the Batch Document.
    """*/
    // PermissionIdentityBody
    public $mappings = array();
    // PermissionIdentityBody
    public $members = array();
    // PermissionIdentityBody
    public $deleted = array();

    // Default constructor used by the deserialization.
    function __construct(){
        /*"""
        Constructor BatchPermissions.
        """*/
        $this->mappings = array();
        $this->members = array();
        $this->deleted = array();
    }

    function __add( &$attr,  $p_PermissionIdentityBodies){
        /*"""
        Add.
        Add a list of p_PermissionIdentityBodies to self[attr].
        :arg attr: name of array to add the identities to (mappings, members, wellKnowns).
        :arg p_PermissionIdentity: PermissionIdentityExpansion.
        """*/
        // Check if correct
        if ($p_PermissionIdentityBodies==null || empty($p_PermissionIdentityBodies)) {
            return;
        }

        if (!is_array($p_PermissionIdentityBodies)){
            $p_PermissionIdentityBodies = array($p_PermissionIdentityBodies);
        }
        if (!is_a($p_PermissionIdentityBodies[0], 'Coveo\\SDK\\SDKPushPHP\\PermissionIdentityBody')){
            //Error( "_add: value is not of type PermissionIdentityBody");
            return;
        }


        $attr = array_merge( $attr, $p_PermissionIdentityBodies);
    }


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function AddMembers( $p_PermissionIdentityBodies){
      //Debug('AddMembers Batch');
        $this->__add($this->members, $p_PermissionIdentityBodies);
    }

    function AddMappings( $p_PermissionIdentityBodies){
      //Debug('AddMappings Batch');
        $this->__add($this->mappings, $p_PermissionIdentityBodies);
    }

    function AddDeletes( $p_PermissionIdentityBodies){
      //Debug('AddDeletes Batch');
        $this->__add($this->deleted, $p_PermissionIdentityBodies);
    }
}
    


class SecurityProviderReference{
    public $id = '';
    public $type = 'SOURCE';
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Default constructor used by the deserialization.

    function __construct(string $p_SourceId, string $p_type){
        /*"""
        Constructor SecurityProviderReference.
        :arg p_SourceId: Source id.
        :arg p_type: "SOURCE"
        """*/
        $this->id = $p_SourceId;
        $this->type = $p_type;
    }
}


class SecurityProvider{
    public $name = '';
    public $nodeRequired = False;
    public $type = '';
    public $referencedBy = array();
    public $cascadingSecurityProviders = array();
}
