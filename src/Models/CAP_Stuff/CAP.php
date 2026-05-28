<?php

namespace App\Models\CAP_Stuff;

require_once 'WriteXML.php';

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
    public $language=array();

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
    public $useingaclass = false;

    function __construct($post = "", $class = false) {
        if(is_array($post) && $class == false) {
            foreach($post as $key => $value) {
                if(property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
        elseif(is_string($post) && $class == true) {
            $this->sent = $post;
            $this->useingaclass = true;
        }
    }


    public function buildCap() {
        $xml = new xml('1.0', 'utf-8');
        $xml->tagOpen('alert', ['xmlns' => 'urn:oasis:names:tc:emergency:cap:1.2']);

        $xml->tagSimple('identifier', $this->identifier);
        $xml->tagSimple('sender', $this->sender);

        if ($this->useingaclass === false && is_array($this->sent)) {
            if (empty($this->sent['plus'])) $this->sent['plus'] = "+";
            $xml->tagSimple(
                'sent',
                date("Y-m-d\TH:i:s", strtotime($this->sent['date'] . " " . $this->sent['time'])) .
                $this->sent['plus'] .
                date("H:i", strtotime($this->sent['UTC']))
            );
        } else {
            $xml->tagSimple('sent', $this->sent);
        }

        $xml->tagSimple('status', $this->status);
        $xml->tagSimple('msgType', $this->msgType);
        $xml->tagSimple('scope', $this->scope);

        $xml->tagSimple('source', $this->source);
        $xml->tagSimple('restriction', $this->restriction);
        $xml->tagSimple('addresses', $this->addresses);
        $xml->tagSimple('code', $this->code);
        $xml->tagSimple('note', $this->note);
        $xml->tagSimple('references', $this->references);
        $xml->tagSimple('incidents', $this->incidents);

        if (is_array($this->language) && count($this->language) > 0) {
            foreach ($this->language as $lang) {
                if (!empty($lang)) {
                    $xml->tagOpen('info');

                    $xml->tagSimple('language', $lang);

                    $xml->tagSimple('category', is_array($this->category) ? implode(',', $this->category) : $this->category);

                    // Handle event, headline, description, instruction as arrays or strings
                    $event = $this->useingaclass === false && is_array($this->event) ? $this->event[$lang] ?? '' : $this->event;
                    $headline = $this->useingaclass === false && is_array($this->headline) ? $this->headline[$lang] ?? '' : $this->headline;
                    $description = $this->useingaclass === false && is_array($this->description) ? $this->description[$lang] ?? '' : $this->description;
                    $instruction = $this->useingaclass === false && is_array($this->instruction) ? $this->instruction[$lang] ?? '' : $this->instruction;

                    $xml->tagSimple('event', $event);
                    $xml->tagSimple('responseType', is_array($this->responseType) ? implode(',', $this->responseType) : $this->responseType);
                    $xml->tagSimple('urgency', $this->urgency);
                    $xml->tagSimple('severity', $this->severity);
                    $xml->tagSimple('certainty', $this->certainty);
                    $xml->tagSimple('audience', $this->audience);

                    // eventCode
                    if (!empty($this->eventCode['valueName'][0])) {
                        foreach ($this->eventCode['valueName'] as $key => $eventCode) {
                            if (!empty($this->eventCode['valueName'][$key])) {
                                $xml->tagOpen('eventCode');
                                $xml->tagSimple('valueName', $this->eventCode['valueName'][$key]);
                                $xml->tagSimple('value', $this->eventCode['value'][$key]);
                                $xml->tagClose('eventCode');
                            }
                        }
                    }

                    // effective, onset, expires
                    if ($this->useingaclass === false && is_array($this->effective)) {
                        $xml->tagSimple(
                            'effective',
                            date("Y-m-d\TH:i:s", strtotime($this->effective['date'] . " " . $this->effective['time'])) .
                            $this->effective['plus'] .
                            date("H:i", strtotime($this->effective['UTC']))
                        );
                    } else {
                        $xml->tagSimple('effective', $this->effective);
                    }
                    if ($this->useingaclass === false && is_array($this->onset)) {
                        $xml->tagSimple (
                            'onset',
                            date("Y-m-d\TH:i:s", strtotime($this->onset['date'] . " " . $this->onset['time'])) .
                            $this->onset['plus'] .
                            date("H:i", strtotime($this->onset['UTC']))
                        );
                    } else {
                        $xml->tagSimple('onset', $this->onset);
                    }
                    if ($this->useingaclass === false && is_array($this->expires)) {
                        $xml->tagSimple(
                            'expires',
                            date("Y-m-d\TH:i:s", strtotime($this->expires['date'] . " " . $this->expires['time'])) .
                            $this->expires['plus'] .
                            date("H:i", strtotime($this->expires['UTC']))
                        );
                    } else {
                        $xml->tagSimple('expires', $this->expires);
                    }

                    $xml->tagSimple('senderName', $this->senderName);
                    $xml->tagSimple('headline', $headline);
                    $xml->tagSimple('description', $description);
                    $xml->tagSimple('instruction', $instruction);

                    $xml->tagSimple('web', $this->web);
                    $xml->tagSimple('contact', $this->contact);

                    // parameter
                    if (!empty($this->parameter['valueName'][0])) {
                        foreach ($this->parameter['valueName'] as $key => $parameter) {
                            if (!empty($this->parameter['valueName'][$key])) {
                                $xml->tagOpen('parameter');
                                $xml->tagSimple('valueName', $this->parameter['valueName'][$key]);
                                $xml->tagSimple('value', $this->parameter['value'][$key]);
                                $xml->tagClose('parameter');
                            }
                        }
                    }

                    // area
                    if (!empty($this->areaDesc) || !empty($this->polygon) || !empty($this->circle) || !is_array($this->geocode)) {
                        $xml->tagOpen('area');
                        $xml->tagSimple('areaDesc', $this->areaDesc);
                        $xml->tagSimple('polygon', is_array($this->polygon) ? implode(' ', $this->polygon) : $this->polygon);
                        $xml->tagSimple('circle', is_array($this->circle) ? implode(' ', $this->circle) : $this->circle);

                        // geocode
                        if (!empty($this->geocode['value'][0])) {
                            foreach ($this->geocode['value'] as $key => $geocode) {
                                if (!empty($this->geocode['value'][$key])) {
                                    $tmp_geocode = explode('<>', $this->geocode['value'][$key]);
                                    $xml->tagOpen('geocode');
                                    $xml->tagSimple('valueName', $tmp_geocode[1] ?? '');
                                    $xml->tagSimple('value', $tmp_geocode[0] ?? '');
                                    $xml->tagClose('geocode');
                                }
                            }
                        }

                        $xml->tagClose('area');
                    }

                    $xml->tagClose('info');
                }
            }
        }

        $xml->tagClose('alert');
        $this->cap = $xml->output();
    }
}