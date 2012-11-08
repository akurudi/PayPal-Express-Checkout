<?php
/**
 * @uses      PayPal Express Checkout Class
 * @author    Adarsh Kurudi
 * @version   3.0
 * @access    Public
 */
class PaypalExpress {


  /**
   * @uses    Holds the paypal sandbox server address - (test or live).
   * @access  Private
   * @var     String
   */
	private $posturl = '';
	
	
  /**
   * @uses    Your API username for the sandbox server.
   * @access  Private
   * @var     String
   */
  private $user = '';
	
	
  /**
   * @uses    Your API password for the sandbox server.
   * @access  Private
   * @var     String
   */
	private $pwd = '';
	
  /**
   * @uses    Your API signature for the sandbox server.
   * @access  Private
   * @var     String
   */
	private $sig = '';
	
	
  /**
   * @uses    Paypal version number.
   * @access  Private
   * @var     String
   */
	private $ver = '0';
	
	
  /**
   * @uses    Holds the current payment action (Authorization, Sale or Order).
   * @access  Private
   * @var     String
   */
  private $type = '';

	/**
   * @uses    Payment action for the transaction (Authorization, Sale or Order).
   * @access  Private
   * @var     Array
   */
  private $setType = array (
    'a'=>'Authorization',
    's'=>'Sale',
	'o'=>'Order',
  );		
	
  /**
   * @uses    Amount of the order including shipping, handling and tax.
   * @access  Private
   * @var     Integer
   */
  private $amt = '0';
	
	
  /**
   * @uses    The URL to which the buyer is redirected after he logs in and approves the transaction.
   * @access  Private
   * @var     String
   */
  private $returnurl = '';
	
	
  /**
   * @uses    The URL to which the buyer is redirected if he does not approve the transaction.
   * @access  Private
   * @var     String
   */
  private $cancelurl = '';
	
  /**
   * @uses    Contains the URLS for submitting a transaction.
   * @access  Private
   * @var     Array
   */
  private $gatewayURL = array (
    'live'=>'https://api.paypal.com/nvp',
    'test'=>'https://api-3t.sandbox.paypal.com/nvp',
  );

	
  /**
   * @uses    Type of API operation being invoked (SetExpressCheckout, GetExpressCheckoutDetails or DoExpressCheckoutPayment).
   * @access  Private
   * @var     String
   */
	private $api = '';
	
	/**
   * @uses    Contains the API names to be set.
   * @access  Private
   * @var     Array
   */
  private $setMethod = array (
    'set'=>'SetExpressCheckout',
    'get'=>'GetExpressCheckoutDetails',
	'do'=>'DoExpressCheckoutPayment',
  );
	
  /**
   * @uses    Holds the post parameters. (The API operation, version, and API signature for the request).
   * @access  Private
   * @var     String
   */
	private $param = '';

	
  /**
   * @uses    Stores the response from the sandbox server.
   * @access  Private
   * @var     String
   */
  private $ch = '';
		
	/**
   * @uses    Stores the values that are returned to the webpage.
   * @access  Private
   * @var     Array
   */
	private $arrparsed = array();
	
	
  /**
   * @uses      Constructor - User paramters with return URL and Cancel URL required to invoke the APIs.
   * @access    Public
   * @param     String $user - Your API username for the sandbox server.
   * @param     String $pwd - Your API password for the sandbox server.
   * @param     String $sig - Your API signature for the sandbox server.
   * @param     String $ver - Paypal version number.
   * @param     String $amt - Amount of the order including shipping, handling and tax.
   * @param     String $returnurl - The URL to which the buyer is redirected after he logs in and approves the transaction.
   * @param     String $cancelurl - The URL to which the buyer is redirected if he does not approve the transaction.
   * @return    None.
   */ 
	public function __construct($user = '',$pwd = '',$sig = '',$ver = '',$amt = '',$returnurl = '',$cancelurl = '') {
		$this->user = urlencode($user);
		$this->pwd = urlencode($pwd);
		$this->sig = urlencode($sig);
		$this->ver = urlencode($ver);
		$this->amt = urlencode($amt);
		$this->returnurl = urlencode($returnurl);
		$this->cancelurl = urlencode($cancelurl);
	}
	
	
  /**
   * @uses      Destructor.
   * @access    Public
   * @param     None.
   * @return    None.
   */
	public function __destruct() {
		unset($this);
	}
	
	
  /**
   * @uses      Sets the posturl to either the test environment or live sandbox URL.
   * @access    Public
   * @param     String $environment - Sandbox type to invoke.
   * @return    None.
   * @example   $PayPal->setEnvironment('test');
   */
	public function setEnvironment($environment = '') {
		if(strtolower($environment) == 'test') {
			$this->posturl = $this->gatewayURL['test'];
		}
		elseif(strtolower($environment) == 'live') {
			$this->posturl = $this->gatewayURL['live'];
		}
	
	}

  /**
   * @uses      Sets the transaction type to either sale, authorization or order type.
   * @access    Public
   * @param     String $type - Type of transactioned being proccessed.
   * @return    None.
   * @example   $PayPal->transactionType('s');
   */
	public function transactionType($type = '') {
		switch (strtolower($type)) {
			case 's' : 
				$this->type = $this->setType['s'];
				break;
			case 'a' : 
				$this->type = $this->setType['a'];
				break;
			case 'o' : 
				$this->type = $this->setType['o'];
				break;
		}
	}

	
  /**
   * @uses      Sets the type of API being invoked.
   * @access    Public
   * @param     String $api - API type to invoke.
   * @return    None.
   * @example   $PayPal->setMethod('set');
   */	
	public function setMethod($api = '') {
		switch (strtolower($api)) {
			case 'set':
				$this->method = urlencode($this->setMethod['set']);
				$this->setExpressCheckout();
				break;
			case 'get':
				$this->method = urlencode($this->setMethod['get']);
				$this->getExpressCheckout();
				break;
			case 'do':
				$this->method = urlencode($this->setMethod['do']);
				$this->doExpressCheckout();
				break;
		}
	}
	
  /**
   * @uses      Sets the NVP post parameters to be posted to the sandbox using the SetExpressCheckout API.
   * @access    Private
   * @param     String $param - Parameters to be appended to the URL as post parameters.
   * @return    None.
   */
	private function setExpressCheckout() {
		$this->param="METHOD=$this->method&USER=$this->user&PWD=$this->pwd&SIGNATURE=$this->sig&VERSION=$this->ver&PAYMENTREQUEST_0_PAYMENTACTION=$this->type&PAYMENTREQUEST_0_AMT=$this->amt&RETURNURL=$this->returnurl&CANCELURL=$this->cancelurl";
	}
	
	
  /**
   * @uses      Sets the NVP post parameters to be posted to the sandbox using the GetExpressCheckoutDetails API.
   * @access    Private
   * @param     String $param - Parameters to be appended to the URL as post parameters.
   * @return    None.
   */
	private function getExpressCheckout() {
		$this->param="METHOD=$this->method&USER=$this->user&PWD=$this->pwd&SIGNATURE=$this->sig&VERSION=$this->ver&TOKEN=$this->token";
	}
	
	
  /**
   * @uses      Sets the NVP post parameters to be posted to the sandbox using the DoExpressChecoutPayment API.
   * @access    Private
   * @param     String $param - Parameters to be appended to the URL as post parameters.
   * @return    None.
   */
	private function doExpressCheckout() {
		$this->param="METHOD=$this->method&USER=$this->user&PWD=$this->pwd&SIGNATURE=$this->sig&VERSION=$this->ver&TOKEN=$this->token&PAYMENTREQUEST_0_PAYMENTACTION=$this->type&PAYMENTREQUEST_0_AMT=$this->amt&PAYERID=$this->payerid";
	}
	
	
  /**
   * @uses      Extracts the token and payer ID returned by the server in the return URL.
   * @access    Public
   * @param     None.
   * @return    None.
   * @example   $PayPal->getReturnParams();
   */
	public function getReturnParams() {
		$this->token=$_GET['token']; //Stores the current token number for the transaction
		$this->payerid=$_GET['PayerID']; //Stores the current Payer ID for the transaction
	}
	
	
  /**
   * @uses      Initializes the curl functions and fetches the response from the server after posting the parameters.
   * @access    Public
   * @param     None.
   * @return    None.
   * @example   $PayPal->ProcessTransaction();
   */
	public function ProcessTransaction() {
	// Uses the CURL library for php to establish a connection,
    // submit the post, and fetch the response.
		$this->ch=curl_init(); //Initiate Curl operation
		curl_setopt($this->ch, CURLOPT_URL, $this->posturl);
		curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($this->ch, CURLOPT_FORBID_REUSE, TRUE); //Forces closure of connection when done
		curl_setopt($this->ch, CURLOPT_POST, 1); //Data sent as POST
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->param); //Send the POST parameters
		$this->result=curl_exec($this->ch); // Execute curl post and store results in $this->result
		if(!$this->result) {
			exit("failed: ".curl_error($this->ch).'('.curl_errno($this->ch).')'); //Check for connection errors and output the error message
		}
		curl_close($this->ch); //Close Curl connection
		//$this->result; //Uncomment to check server response message
	}
	
  /**
   * @uses      Extracts the response from the server and parses into an array.
   * @access    Public
   * @param     None.
   * @return    Array - Acknowledge, Token and Payer ID.
   * @example   $PayPal->extractResponse();
   */
	public function extractResponse() {
		$this->arr=explode('&',$this->result);
		$this->arrde=array();
		foreach($this->arr as $i) {
			array_push($this->arrde,urldecode($i));
		}
		foreach($this->arrde as $i) {
			$this->temp=explode('=',$i);
			$this->arrparsed[$this->temp[0]]=$this->temp[1];
		}
		if((0 == sizeof($this->arrparsed)) || !array_key_exists('ACK', $this->arrparsed)) {
			exit("Invalid HTTP Response for POST request to $this->posturl.");
		}
		//Return only the values that are needed by the user to proceeded.
		$returnvalues = array(
			'ACK'=>$this->arrparsed['ACK'],
			'TOKEN'=>$this->arrparsed['TOKEN'],
			'PAYERID'=>$this->arrparsed['PAYERID'],
		);
		return $returnvalues;
	}
	
  /**
   * @uses      Appends the token number and redirects to the PayPal login page.
   * @access    Public
   * @param     None.
   * @return    None.
   * @example   $PayPal->loginPage();
   */
	public function loginPage() {
		$this->token=$this->arrparsed['TOKEN'];
		$this->paypal="https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=$this->token";
		header("Location: $this->paypal");
	}
}
?>