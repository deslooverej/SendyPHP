<?php

namespace SendyPHP;

/**
 * Sendy
 *
 * This Sendy PHP Class connects to the Sendy API that sends mails using Amazon SES.
 *
 * @author Jacob Bennett <me@jakebennett.net>
 * @author Jeroen Desloovere <info@jeroendesloovere.be>
 */
class SendyPHP
{
	/**
	 * API key
	 *
	 * @param string
	 */
    protected $apiKey;

	/**
	 * API url
	 *
	 * @param string
	 */
	protected $apiUrl;

	/**
	 * List ID
	 *
	 * @param string
	 */
	protected $listId;

	/**
	 * Construct
	 *
	 * @return void
	 * @param string $apiKey
	 * @param string $apiUrl
	 * @param string $listId
	 */
	public function __construct($apiKey, $apiUrl, $listId)
	{
	    // we define our parameters	
		$this->apiKey = (string) $apiKey;
		$this->apiUrl = (string) $apiUrl;
		$this->listId = (string) $listId;
	}

	/**
	 * Do call
	 *
	 * @param string $method
	 * @param array $parameters
	 * @return array
	 */
	protected function doCall($method, $parameters)
	{
		// init url
		$url = $this->apiUrl . 'api/' . $method . '.php';

		// define api key
		$parameters['api_key'] = $this->apiKey;

		// define list id if not already set
		if(!isset($parameters['list_id'])) $parameters['list_id'] = $this->listId;

		// open curl connection
		$ch = curl_init();

		// define curl settings
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);

		// get results
		$result = curl_exec($ch);

		// close curl connection
		curl_close($ch);

        // return result
		return $result;
	}

    /**
     * Get list id
     *
     * @return string
     */
    public function getListId()
    {
        return $this->$listId;
    }

    /**
     * Get subscribers count
     *
     * @return int
     * @param string $listId
     */
    public function getSubscribersCount($listId = null)
    {
        // init parameters
	    $parameters = array();

	    // listId is set
	    if (isset($listId)) {
	        // add to parameters
	        $parameters[] = $listId;
	    }

		// return int
		return $this->doCall(
		    'subscribers/active-subscriber-count',
		    $parameters
		)
    }

	/**
	 * Has this list subscribers?
	 *
	 * @return bool
	 * @return string listId
	 */
	public function hasSubscribers($listId = null)
	{
	    // return bool
		return is_numeric($this->getSubscribersCount($listId));
	}

    /**
     * Set list id
     *
     * @return void
     * @param string
     */
    public function setListId($listId)
    {
        // define list id
        $this->$listId = (string) $listId;
    }

    /**
     * Subscribe a user
     *
     * @return array
     * @param array $values
     */
    public function subscribe(array $values)
    {
        // do subscribe call
        $result = strval($this->doCall(
            'subscribe',
            $values
        ));

        // handle results
        switch ($result) {
            case '1':
                return array(
                    'status' => true,
                    'message' => 'Subscribed'
                    );
                break;

            case 'Already subscribed.':
                return array(
                    'status' => true,
                    'message' => 'Already subscribed.'
                    );
                break;
            
            default:
                return array(
                    'status' => false,
                    'message' => $result
                    );
                break;
        }
    }

    /**
     * Unsubscribe
     *
     * @return array
     * @param string $email
     */
    public function unsubscribe($email)
    {
        // do unsubscribe call
        $result = strval($this->doCall(
            'unsubscribe',
            array(
                'email' => $email
            )
        );

        // handle results
        switch ($result) {
            case '1':
                return array(
                    'status' => true,
                    'message' => 'Unsubscribed'
                    );
                break;
            
            default:
                return array(
                    'status' => false,
                    'message' => $result
                    );
                break;
        }
    }

    /**
     * Sub status
     *
     * @param string $email
     */
    public function substatus($email)
    {
        // do call for the subscribers status
        $result = $this->doCall(
            'subscribers/subscription-status',
            array(
                'email' => $email
            )
        );

        // handle the results
        switch ($result) {
            case 'Subscribed':
            case 'Unsubscribed':
            case 'Unconfirmed':
            case 'Bounced':
            case 'Soft bounced':
            case 'Complained':
                return array(
                    'status' => true,
                    'message' => $result
                    );
                break;
            
            default:
                return array(
                    'status' => false,
                    'message' => $result
                    );
                break;
        }

    }

    /**
     * Su
     */
    public function subcount($list = "")
    {
        $method = 'api/subscribers/active-subscriber-count.php';

        //handle exceptions
        if ($list== "" && $this->$listId == "") {
            throw new SendyException("method [subcount] requires parameter [list] or [$this->$listId] to be set.", 1);
        }

        //if a list is passed in use it, otherwise use $this->$listId
        if ($list == "") {
            $list = $this->$listId;
        }

        //Send request for subcount
        $result = $this->doCall($method, array(
            'api_key' => $this->api_key,
            '$listId' => $list
        ));
        
        //Handle the results
        if (is_int($result)) {
            return array(
                'status' => true,
                'message' => $result
            );
        }

        //Error
        return array(
            'status' => false,
            'message' => $result
        );
    }
}


/**
 * Sendy Exception
 *
 * @author Jeroen Desloovere <info@jeroendesloovere.be>
 */
class SendyException extends Exception {}
