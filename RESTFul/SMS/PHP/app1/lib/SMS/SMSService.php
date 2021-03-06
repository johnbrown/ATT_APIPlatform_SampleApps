<?php
namespace Att\Api\SMS;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 */

/**
 * SMS Library
 * 
 * PHP version 5.4+
 * 
 * LICENSE: Licensed by AT&T under the 'Software Development Kit Tools 
 * Agreement.' 2013. 
 * TERMS AND CONDITIONS FOR USE, REPRODUCTION, AND DISTRIBUTIONS:
 * http://developer.att.com/sdk_agreement/
 *
 * Copyright 2013 AT&T Intellectual Property. All rights reserved.
 * For more information contact developer.support@att.com
 * 
 * @category  API
 * @package   SMS 
 * @author    pk9069
 * @copyright 2013 AT&T Intellectual Property
 * @license   http://developer.att.com/sdk_agreement AT&amp;T License
 * @link      http://developer.att.com
 */

require_once __DIR__ . '../../Srvc/APIService.php';
require_once __DIR__ . '/SendSMSResponse.php';
require_once __DIR__ . '/GetSMSResponse.php';
require_once __DIR__ . '/DeliveryStatus.php';

use Att\Api\OAuth\OAuthToken;
use Att\Api\Restful\HttpPost;
use Att\Api\Restful\RestfulRequest;
use Att\Api\Srvc\APIService;
use Att\Api\Srvc\Service;

/**
 * Used to interact with version 3 of the SMS API.
 *
 * @category API
 * @package  SMS
 * @author   pk9069
 * @license  http://developer.att.com/sdk_agreement AT&amp;T License
 * @version  Release: @package_version@ 
 * @link     https://developer.att.com/docs/apis/rest/3/SMS
 */
class SMSService extends APIService
{
    /**
     * Creates a SMSService object that can be used to interact with
     * the SMS API.
     *
     * @param string     $FQDN  fully qualified domain name to which requests 
     *                          will be sent
     * @param OAuthToken $token OAuth token used for authorization 
     */
    public function __construct($FQDN, OAuthToken $token) 
    {
        parent::__construct($FQDN, $token); 
    }


    /** 
     * Sends a request to the API for sending a SMS to the specified address.
     *
     * @param string  $addr                 address to which SMS should be sent. 
     * @param string  $msg                  SMS message body to send.
     * @param boolean $notifyDeliveryStatus whether the API should sent a
     *                                      notification after delivery.
     *
     * @return SendSMSResponse API response.
     * @throws ServiceException if API request was not successful.
     */
    public function sendSMS($addr, $msg, $notifyDeliveryStatus = true) 
    {
        $vals = array(
            'address' => $addr, 
            'message' => $msg, 
            'notifyDeliveryStatus' => $notifyDeliveryStatus
        );
        $jsobj = array('outboundSMSRequest' => $vals);
        $jvals = json_encode($jsobj);

        $endpoint = $this->getFqdn() . '/sms/v3/messaging/outbox';

        $req = new RESTFulRequest($endpoint);

        $req
            ->setAuthorizationHeader($this->getToken())
            ->setHeader('Accept', 'application/json')
            ->setHeader('Content-Type', 'application/json');

        $httpPost = new HttpPost();

        $httpPost->setBody($jvals);

        $result = $req->sendHttpPost($httpPost);

        $arr = Service::parseJson($result);
        return SendSMSResponse::fromArray($arr);
    }

    /**
     * Sends a request to the API for getting SMS Delivery status. 
     *
     * @param string $smsId SMS id for which to get delivery status.
     *
     * @return SMSStatusResponse API response.
     * @throws ServiceException if API request was not successful.
     */
    public function getSMSDeliveryStatus($smsId)
    {
        $encodedId = urlencode($smsId);
        $endpoint = $this->getFqdn() . '/sms/v3/messaging/outbox/' . $encodedId;  
        $req = new RESTFulRequest($endpoint);

        $result = $req
            ->setAuthorizationHeader($this->getToken())
            ->setHeader('Accept', 'application/json')
            ->setHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->sendHttpGet();

        $arr = Service::parseJson($result);
        return DeliveryStatus::fromArray($arr);
    }

    /**
     * Sends a request to the API for getting any SMS messages that were sent 
     * to the specified short code. 
     *
     * @param string $shortCode gets messages sent to this short code
     *
     * @return GetSMSResponse API response
     * @throws ServiceException if API request was not successful
     */
    public function getMessages($shortCode) 
    {
        $endpoint = $this->getFqdn() . '/sms/v3/messaging/inbox/'
            . urlencode($shortCode);  

        $req = new RESTFulRequest($endpoint);
        
        $result = $req
            ->setAuthorizationHeader($this->getToken())
            ->setHeader('Accept', 'application/json')
            ->setHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->sendHttpGet();
        
        $arr = Service::parseJson($result);
        return GetSMSResponse::fromArray($arr);
    } 
}
?>
