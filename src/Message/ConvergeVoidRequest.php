<?php

namespace Omnipay\Elavon\Message;

/**
 * Elavon's Converge Void Request
 *
 * This class processes form post requests using the Elavon/Converge gateway as documented here:
 * https://resourcecentre.elavonpaymentgateway.com/index.php/download-developer-guide
 *
 * Also here: https://www.convergepay.com/converge-webapp/developer/#/welcome
 *
 * ### Test Mode
 *
 * In order to begin testing you will need the following parameters from Elavon/Converge:
 *
 * * merchantId, aka ssl_merchant_id
 * * username, aka ssl_user_id
 * * password, aka ssl_pin
 *
 * These parameters are issued for a short time only.  You need to contact Converge to request an extension
 * a few days before these parameters expire.
 *
 * ### Example
 *
 * #### Initialize Gateway
 *
 * <code>
 * //
 * // Put your gateway credentials here.
 * //
 * $credentials = array(
 *     'merchantId'        => '000000',
 *     'username'          => 'USERNAME',
 *     'password'          => 'PASSWORD'
 *     'testMode'          => true,            // Or false if you want to test production mode
 * );
 *
 * // Create a gateway object
 * // (routes to GatewayFactory::create)
 * $gateway = Omnipay::create('Elavon_Converge');
 *
 * // Initialise the gateway
 * $gateway->initialize($credentials);
 * </code>
 *
 * #### Direct Credit Card Void
 *
 * See the ConvergePurchaseRequest class for the details of creating a purchase.
 *
 * <code>
 * // Do void transaction on the gateway
 * try {
 *     $transaction = $gateway->void(array(
 *         'transactionReference'  => $sale_id,
 *     ));
 *     $response = $transaction->send();
 *     $data = $response->getData();
 *     echo "Gateway void response data == " . print_r($data, true) . "\n";
 *
 *     if ($response->isSuccessful()) {
 *         echo "Void transaction was successful!\n";
 *     }
 * } catch (\Exception $e) {
 *     echo "Exception caught while attempting void.\n";
 *     echo "Exception type == " . get_class($e) . "\n";
 *     echo "Message == " . $e->getMessage() . "\n";
 * }
 * </code>
 *
 * ### Quirks
 *
 * Two additional parameters need to be sent with every request.  These should be set to defaults
 * in the Gateway class but in case they are not, set them to the following values as shown on
 * every transaction request before calling $transaction->send():
 *
 * <code>
 * $transaction->setSslShowForm('false');
 * $transaction->setSslResultFormat('ASCII');
 * </code>
 *
 * @link https://www.myvirtualmerchant.com/VirtualMerchant/
 * @link https://resourcecentre.elavonpaymentgateway.com/index.php/download-developer-guide
 * @see \Omnipay\Elavon\ConvergeGateway
 */
class ConvergeVoidRequest extends ConvergeAbstractRequest
{
    protected $transactionType = 'ccvoid';

    public function getData()
    {
        $this->validate('transactionReference');

        $data = array(
            'ssl_result_format' => 'ASCII',
            'ssl_transaction_type' => $this->transactionType,
            'ssl_txn_id' => $this->getTransactionReference(),
        );

        return array_merge($this->getBaseData(), $data);
    }

    public function sendData($data)
    {
        $httpResponse = $this->httpClient->post($this->getEndpoint() . '/process.do', null, http_build_query($data))
            ->setHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->send();

        return $this->createResponse($httpResponse->getBody());
    }
}
