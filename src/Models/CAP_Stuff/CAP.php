<?php

namespace App\Models\CAP_Stuff;

/**
 * Common Alerting Protocol (CAP) Model
 * 
 * Represents the structure of a CAP message as per the CAP 1.2 data dictionary.
 */
class CAP {
    public $output = "CAP";
    public $cap = "";

    //datele din CAP 1.2 data dictionary, structurare pe: alert, info, resource, area (https://docs.oasis-open.org/emergency/cap/v1.2/CAP-v1.2-os.html) -> 3.1
    /**
     * @var string The identifier of the alert message (REQUIRED).
     * A number or string uniquely identifying this message, assigned by the sender.
     * MUST NOT include spaces, commas or restricted characters (< and &).
     */
    public $identifier;

    /**
     * @var string The identifier of the sender of the alert message (REQUIRED).
     * Identifies the originator of this alert. Guaranteed by assigner to be unique globally.
     */
    public $sender;

    /**
     * @var string The time and date of the origination of the alert message (REQUIRED).
     * Format: DateTime Data Type (e.g., "2002-05-24T16:49:00-07:00").
     */
    public $sent;

    /**
     * @var string The code denoting the appropriate handling of the alert message (REQUIRED).
     * Values: "Actual", "Exercise", "System", "Test", "Draft".
     */
    public $status;

    /**
     * @var string The code denoting the nature of the alert message (REQUIRED).
     * Values: "Alert", "Update", "Cancel", "Ack", "Error".
     */
    public $msgType;

    /**
     * @var string|null The text identifying the source of the alert message (OPTIONAL).
     * The particular source of this alert; e.g., an operator or a specific device.
     */
    public $source;

    /**
     * @var string The code denoting the intended distribution of the alert message (REQUIRED).
     * Values: "Public", "Restricted", "Private".
     */
    public $scope;

    /**
     * @var string|null The text describing the rule for limiting distribution of the restricted alert message (CONDITIONAL).
     * Used when <scope> value is "Restricted".
     */
    public $restriction;

    /**
     * @var string|null The group listing of intended recipients of the alert message (CONDITIONAL).
     * Required when <scope> is "Private", optional otherwise. Multiple space-delimited addresses MAY be included.
     */
    public $addresses;

    /**
     * @var array|null The code denoting the special handling of the alert message (OPTIONAL).
     * Any user-defined flag or special code. Multiple instances MAY occur.
     */
    public $code;

    /**
     * @var string|null The text describing the purpose or significance of the alert message (OPTIONAL).
     */
    public $note;

    /**
     * @var string|null The group listing identifying earlier message(s) referenced by the alert message (OPTIONAL).
     * Format: sender,identifier,sent. Multiple separated by whitespace.
     */
    public $references;

    /**
     * @var string|null The group listing naming the referent incident(s) of the alert message (OPTIONAL).
     * Used to collate multiple messages referring to different aspects of the same incident.
     */
    public $incidents;


    // ==========================================
    // 3.2.2 "info" Element and Sub-elements
    // ==========================================

    /**
     * @var array|null The container for all component parts of the info sub-element of the alert message (OPTIONAL).
     * Multiple occurrences are permitted.
     */
    public $info;

    /**
     * @var string|null The code denoting the language of the info sub-element of the alert message (OPTIONAL).
     * Default: "en-US".
     */
    public $language;

    /**
     * @var array The code denoting the category of the subject event of the alert message (REQUIRED).
     * Values: "Geo", "Met", "Safety", "Security", "Rescue", "Fire", "Health", "Env", "Transport", "Infra", "CBRNE", "Other".
     */
    public $category;

    /**
     * @var string The text denoting the type of the subject event of the alert message (REQUIRED).
     */
    public $event;

    /**
     * @var array|null The code denoting the type of action recommended for the target audience (OPTIONAL).
     * Values: "Shelter", "Evacuate", "Prepare", "Execute", "Avoid", "Monitor", "Assess", "AllClear", "None".
     */
    public $responseType;

    /**
     * @var string The code denoting the urgency of the subject event of the alert message (REQUIRED).
     * Values: "Immediate", "Expected", "Future", "Past", "Unknown".
     */
    public $urgency;

    /**
     * @var string The code denoting the severity of the subject event of the alert message (REQUIRED).
     * Values: "Extreme", "Severe", "Moderate", "Minor", "Unknown".
     */
    public $severity;

    /**
     * @var string The code denoting the certainty of the subject event of the alert message (REQUIRED).
     * Values: "Observed", "Likely", "Possible", "Unlikely", "Unknown".
     */
    public $certainty;

    /**
     * @var string|null The text describing the intended audience of the alert message (OPTIONAL).
     */
    public $audience;

    /**
     * @var array|null A system-specific code identifying the event type of the alert message (OPTIONAL).
     */
    public $eventCode;

    /**
     * @var string|null The effective time of the information of the alert message (OPTIONAL).
     */
    public $effective;

    /**
     * @var string|null The expected time of the beginning of the subject event of the alert message (OPTIONAL).
     */
    public $onset;

    /**
     * @var string|null The expiry time of the information of the alert message (OPTIONAL).
     */
    public $expires;

    /**
     * @var string|null The text naming the originator of the alert message (OPTIONAL).
     */
    public $senderName;

    /**
     * @var string|null The text headline of the alert message (OPTIONAL).
     * A brief human-readable headline.
     */
    public $headline;

    /**
     * @var string|null The text describing the subject event of the alert message (OPTIONAL).
     */
    public $description;

    /**
     * @var string|null The text describing the recommended action to be taken by recipients of the alert message (OPTIONAL).
     */
    public $instruction;

    /**
     * @var string|null The identifier of the hyperlink associating additional information with the alert message (OPTIONAL).
     */
    public $web;

    /**
     * @var string|null The text describing the contact for follow-up and confirmation of the alert message (OPTIONAL).
     */
    public $contact;

    /**
     * @var array|null A system-specific additional parameter associated with the alert message (OPTIONAL).
     */
    public $parameter;


    // ==========================================
    // 3.2.3 "resource" Element and Sub-elements
    // ==========================================

    /**
     * @var array|null The container for all component parts of the resource sub-element (OPTIONAL).
     */
    public $resource;

    /**
     * @var string The text describing the type and content of the resource file (REQUIRED).
     */
    public $resourceDesc;

    /**
     * @var string The identifier of the MIME content type and sub-type describing the resource file (REQUIRED).
     */
    public $mimeType;

    /**
     * @var int|null The integer indicating the size of the resource file in bytes (OPTIONAL).
     */
    public $size;

    /**
     * @var string|null The identifier of the hyperlink for the resource file (OPTIONAL).
     */
    public $uri;

    /**
     * @var string|null The base-64 encoded data content of the resource file (CONDITIONAL).
     */
    public $derefUri;

    /**
     * @var string|null The code representing the digital digest (“hash”) computed from the resource file (OPTIONAL).
     * Calculated using SHA-1.
     */
    public $digest;


    // ==========================================
    // 3.2.4 "area" Element and Sub-elements
    // ==========================================

    /**
     * @var array|null The container for all component parts of the area sub-element (OPTIONAL).
     */
    public $area;

    /**
     * @var string The text describing the affected area of the alert message (REQUIRED).
     */
    public $areaDesc;

    /**
     * @var array|null The paired values of points defining a polygon that delineates the affected area (OPTIONAL).
     */
    public $polygon;

    /**
     * @var array|null The paired values of a point and radius delineating the affected area (OPTIONAL).
     */
    public $circle;

    /**
     * @var array|null The geographic code delineating the affected area of the alert message (OPTIONAL).
     */
    public $geocode;

    /**
     * @var string|null The specific or minimum altitude of the affected area in feet above mean sea level (OPTIONAL).
     */
    public $altitude;

    /**
     * @var string|null The maximum altitude of the affected area in feet above mean sea level (CONDITIONAL).
     * MUST NOT be used except in combination with the <altitude> element.
     */
    public $ceiling;

    function __construct($post = "", $class = false) {
        if(is_array($post) && $class == false) {
            f
        }
    }

}
