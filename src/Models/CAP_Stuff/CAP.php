<?php

namespace App\Models\CAP_Stuff;

use App\Models\CAP_Stuff\WriteXML;

/**
 * Common Alerting Protocol (CAP) Model
 * 
 * Represents the structure of a CAP message as per the CAP 1.2 data dictionary.
 * This class is a data container with a method to build the final XML.
 */
class CAP {
    // Properties are public to be easily set by other parts of the application.
    // They correspond to the fields in the CAP 1.2 standard.
    public $output = "CAP";
    public $cap = "";

    //region CAP Properties
    /**
     * @var string The identifier of the alert message (REQUIRED).
     */
    public $identifier;

    /**
     * @var string The identifier of the sender of the alert message (REQUIRED).
     */
    public $sender;

    /**
     * @var string The time and date of the origination of the alert message (REQUIRED).
     */
    public $sent;

    /**
     * @var string The code denoting the appropriate handling of the alert message (REQUIRED).
     */
    public $status;

    /**
     * @var string The code denoting the nature of the alert message (REQUIRED).
     */
    public $msgType;

    /**
     * @var string|null The text identifying the source of the alert message (OPTIONAL).
     */
    public $source;

    /**
     * @var string The code denoting the intended distribution of the alert message (REQUIRED).
     */
    public $scope;

    /**
     * @var string|null The text describing the rule for limiting distribution of the restricted alert message (CONDITIONAL).
     */
    public $restriction;

    /**
     * @var string|null The group listing of intended recipients of the alert message (CONDITIONAL).
     */
    public $addresses;

    /**
     * @var array|null The code denoting the special handling of the alert message (OPTIONAL).
     */
    public $code;

    /**
     * @var string|null The text describing the purpose or significance of the alert message (OPTIONAL).
     */
    public $note;

    /**
     * @var string|null The group listing identifying earlier message(s) referenced by the alert message (OPTIONAL).
     */
    public $references;

    /**
     * @var string|null The group listing naming the referent incident(s) of the alert message (OPTIONAL).
     */
    public $incidents;

    // --- "info" Element Properties ---

    /**
     * @var string|null The code denoting the language of the info sub-element of the alert message (OPTIONAL).
     */
    public $language=array();

    /**
     * @var array The code denoting the category of the subject event of the alert message (REQUIRED).
     */
    public $category;

    /**
     * @var string The text denoting the type of the subject event of the alert message (REQUIRED).
     */
    public $event;

    /**
     * @var array|null The code denoting the type of action recommended for the target audience (OPTIONAL).
     */
    public $responseType;

    /**
     * @var string The code denoting the urgency of the subject event of the alert message (REQUIRED).
     */
    public $urgency;

    /**
     * @var string The code denoting the severity of the subject event of the alert message (REQUIRED).
     */
    public $severity;

    /**
     * @var string The code denoting the certainty of the subject event of the alert message (REQUIRED).
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

    // --- "resource" Element Properties ---

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
     */
    public $digest;

    // --- "area" Element Properties ---

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
     */
    public $ceiling;
    //endregion

    public $useingaclass = false;

    function __construct($post = "", $class = false) {
        // This constructor has two modes:
        // 1. If $post is an array, it populates the CAP object's properties from the array keys.
        //    This is used when creating a CAP message from form data.
        if(is_array($post) && $class == false) {
            foreach($post as $key => $value) {
                if(property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
        // 2. If $class is true, it's a simplified mode for creating a CAP object programmatically.
        //    It just sets the 'sent' time.
        elseif(is_string($post) && $class == true) {
            $this->sent = $post;
            $this->useingaclass = true;
        }
    }


    public function buildCap() {
        // Initialize the XML writer.
        $xml = new WriteXML('1.0', 'utf-8');
        $xml->tagOpen('alert', ['xmlns' => 'urn:oasis:names:tc:emergency:cap:1.2']);

        // --- Main Alert Block ---
        // Builds the core metadata for the alert message.
        $xml->tagSimple('identifier', $this->identifier);
        $xml->tagSimple('sender', $this->sender);

        // The 'sent' timestamp can be either a pre-formatted string or an array of components.
        if ($this->useingaclass === false && is_array($this->sent)) {
            // Assemble the timestamp from date, time, and UTC offset components.
            if (empty($this->sent['plus'])) $this->sent['plus'] = "+";
            $xml->tagSimple(
                'sent',
                date("Y-m-d\TH:i:s", strtotime($this->sent['date'] . " " . $this->sent['time'])) .
                $this->sent['plus'] .
                date("H:i", strtotime($this->sent['UTC']))
            );
        } else {
            // Use the 'sent' value directly if it's already a formatted string.
            $xml->tagSimple('sent', $this->sent);
        }

        $xml->tagSimple('status', $this->status);
        $xml->tagSimple('msgType', $this->msgType);
        $xml->tagSimple('scope', $this->scope);
        $xml->tagSimple('source', $this->source);
        $xml->tagSimple('restriction', $this->restriction);
        $xml->tagSimple('addresses', $this->addresses);
        $xml->tagSimple('code', is_array($this->code) ? implode(' ', $this->code) : $this->code);
        $xml->tagSimple('note', $this->note);
        $xml->tagSimple('references', $this->references);
        $xml->tagSimple('incidents', $this->incidents);

        // --- Info Block ---
        // A CAP message can contain multiple 'info' blocks, for example, one for each language.
        // This loop builds an 'info' block for each language provided in the 'language' array.
        if (is_array($this->language) && count($this->language) > 0) {
            foreach ($this->language as $lang) {
                if (!empty($lang)) {
                    $xml->tagOpen('info');

                    $xml->tagSimple('language', $lang);
                    $xml->tagSimple('category', is_array($this->category) ? implode(',', $this->category) : $this->category);

                    // These fields can be multi-language, stored as an associative array with language codes as keys.
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

                    // Build <eventCode> sub-tags from the 'eventCode' array.
                    if (!empty($this->eventCode['valueName']) && is_array($this->eventCode['valueName'])) {
                        foreach ($this->eventCode['valueName'] as $key => $valueName) {
                            if (!empty($valueName)) {
                                $xml->tagOpen('eventCode');
                                $xml->tagSimple('valueName', $valueName);
                                $xml->tagSimple('value', $this->eventCode['value'][$key] ?? '');
                                $xml->tagClose('eventCode');
                            }
                        }
                    }

                    // Format date-time fields, which can be arrays of components or pre-formatted strings.
                    if ($this->useingaclass === false && is_array($this->effective)) {
                        $xml->tagSimple(
                            'effective',
                            date("Y-m-d\TH:i:s", strtotime($this->effective['date'] . " " . $this->effective['time'])) .
                            ($this->effective['plus'] ?? '+') .
                            date("H:i", strtotime($this->effective['UTC']))
                        );
                    } else {
                        $xml->tagSimple('effective', $this->effective);
                    }
                    if ($this->useingaclass === false && is_array($this->onset)) {
                        $xml->tagSimple (
                            'onset',
                            date("Y-m-d\TH:i:s", strtotime($this->onset['date'] . " " . $this->onset['time'])) .
                            ($this->onset['plus'] ?? '+') .
                            date("H:i", strtotime($this->onset['UTC']))
                        );
                    } else {
                        $xml->tagSimple('onset', $this->onset);
                    }
                    if ($this->useingaclass === false && is_array($this->expires)) {
                        $xml->tagSimple(
                            'expires',
                            date("Y-m-d\TH:i:s", strtotime($this->expires['date'] . " " . $this->expires['time'])) .
                            ($this->expires['plus'] ?? '+') .
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

                    // Build <parameter> sub-tags from the 'parameter' array.
                    if (!empty($this->parameter['valueName']) && is_array($this->parameter['valueName'])) {
                        foreach ($this->parameter['valueName'] as $key => $valueName) {
                            if (!empty($valueName)) {
                                $xml->tagOpen('parameter');
                                $xml->tagSimple('valueName', $valueName);
                                $xml->tagSimple('value', $this->parameter['value'][$key] ?? '');
                                $xml->tagClose('parameter');
                            }
                        }
                    }

                    // --- Area Block ---
                    // Builds the 'area' block describing the affected geographic region.
                    if (!empty($this->areaDesc) || !empty($this->polygon) || !empty($this->circle) || !empty($this->geocode)) {
                        $xml->tagOpen('area');
                        $xml->tagSimple('areaDesc', $this->areaDesc);
                        $xml->tagSimple('polygon', is_array($this->polygon) ? implode(' ', $this->polygon) : $this->polygon);
                        $xml->tagSimple('circle', is_array($this->circle) ? implode(' ', $this->circle) : $this->circle);

                        // Build <geocode> sub-tags from the 'geocode' array.
                        if (!empty($this->geocode['value']) && is_array($this->geocode['value'])) {
                            foreach ($this->geocode['value'] as $key => $geocodeValue) {
                                if (!empty($geocodeValue)) {
                                    $tmp_geocode = explode('<>', $geocodeValue);
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