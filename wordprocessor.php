<?php

define('TEXT_PIPE', '1');
define('TEXT_GENERATE', '2');
define('TEXT_API', '3');

// Get the command line arguments
array_shift($argv);

// set to the user defined error handler
$custom_error_handler = set_error_handler("customErrorHandler");

if(count($argv) == 0)
{
	printUsage();
}
else
{
	$mode = $argv[0];
	$startTime;
	$endTime;
	$inputText = null;

	if(trim($mode) == TEXT_PIPE)
	{
		$startTime = time();
		while (false !== ($line = fgets(STDIN)))
		{
    			$inputText .= $line;
		}

		if(!$inputText)
		{
			trigger_error("The input text was empty!");
		}
		else
		{
			$tt = new TextTokenizer($inputText, true, " -?/,;:.!()\*'[]@#$%&+=|~\n\r\t{}<>\"\\\'");
			$tt->tokenize(); 
			$tt->printMap();
			$endTime = time();

			echo "** RunTime: ".($endTime-$startTime)." secs";
			echo "\n";			
		}
	}
	elseif(trim($mode) == TEXT_GENERATE)
	{
		$startTime = time();
		for($i=0; $i<$argv[1]; $i++)
		{
			$inputText .= ' ';
			$inputText .= getRandomString();
		}

		if(!$inputText)
		{
			trigger_error("There was a problem generating random text!");
		}
		else
		{
			$tt = new TextTokenizer($inputText, false, ' ');
			$tt->tokenize(); 
			$tt->printMap();
			$endTime = time();

			echo "** RunTime: ".($endTime-$startTime)." secs";
			echo "\n";
		}
	}
	elseif(trim($mode) == TEXT_API)
	{
		$startTime = time();
		$inputText = file_get_contents('http://www.random.org/strings/?num=1000&len=20&digits=on&upperalpha=on&loweralpha=on&unique=off&format=plain&rnd=new');

		if(!$inputText)
		{
			trigger_error("There was a problem generating random text!");
		}
		else
		{
			$tt = new TextTokenizer($inputText, false, '0');
			//need to remove line breaks from the returned strings as we are using '0' as word boundary
			$tt->tokenize(PHP_EOL, '');
			$tt->printMap();
			$endTime = time();

			echo "** RunTime: ".($endTime-$startTime)." secs";
			echo "\n";			
		}
	}
	else
	{
		printUsage();
	}
}

function printUsage()
{
	echo "\n";
	echo "Usage: \n";
	echo "'prompt$ cat {path_to_file} | php wordprocessor.php 1' : Pipe text to STDIN\n";
	echo "'prompt$ php wordprocessor.php 2 {num_of_words}' : Generates words randomly from within PHP\n";
	echo "'prompt$ php wordprocessor.php 3' : GETs 1000 words from random.org API\n";
	echo "\n";
}

function getRandomString() 
{
	$length = rand(1, 10);
	$charSet = "abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ";
	$charSetCount = strlen($charSet);
	
	$result = "";
	
	for($i = 0; $i < $length; $i++) 
	{
		$index = mt_rand(0, $charSetCount - 1);
		$result .= $charSet[$index];
	}
	return $result;
}

// custom error handler function
function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) 
    {
        // This error code is not included in error_reporting
        return;
    }

    switch ($errno) 
    {
		case E_USER_ERROR:
			echo "CUSTOM ERROR: [$errno] $errstr\n";
			echo "  Fatal error on line $errline in file $errfile";
			echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")\n";
			echo "Aborting...\n";
			exit(1);
			break;
		
		case E_USER_WARNING:
			echo "CUSTOM WARNING: [$errno] $errstr\n";
			break;
		
		case E_USER_NOTICE:
			echo "CUSTOM NOTICE [$errno] $errstr\n";
			break;
		
		default:
			echo "Unknown error type: [$errno] $errstr\n";
			break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}


/*  TextTokenizer Class - Tokenizes text passed in constructor & counts occurances of words. 
* 	The constructor will initialise the following modes: (Parameters shown)
*	1. $inputText (string)		:  	Text to be processed and counted
*	2. $filterNumers (boolean)	:	Flag to filter out alphanumeric words
*	3. $delimiter (string)		:	String to be used as delimiter for tokenising the text
*
*	The function Tokenize() uses the delimiter to tokenize the text and count the word occurances.
*	Tokenize() function additionally takes $search & $replace text in case additional processing is required on the tokens.
*/
class TextTokenizer
{
    private $inputText = null;
    private $delimiter;
    private $innerMap = array();
    private $filterNumbers;

	function __construct($inputText, $filterNumbers, $delimiter) 
	{ 
		$this->inputText = $inputText;
		$this->filterNumbers = $filterNumbers;
		$this->delimiter = $delimiter;
	}

	public function __destruct() 
	{
		unset($this);
	}

    function tokenize($search = null, $replace = '')
    {
    	try
    	{
		$tok = strtok($this->inputText, $this->delimiter);
	    	while ($tok !== false) 
	    	{	
	    		//if search string is passed, search and filter for custom text processing of tokens
	    		if($search)
	    		{
	    			$tok = str_replace($search, $replace, $tok);
	    		}
	    		
	    		//trim any extra spaces or newlines
	    		$tok = trim(strtolower($tok));
	    		
	    		//for filtering out alphanumeric words
	    		if($this->filterNumbers && !preg_match("/([a-zA-Z]+)/", $tok))
	    		{
	    			//enable echo to check if its filtering correctly
	    			//echo $tok;
	    			//echo "\n";
	    		}
	    		else
	    		{
				if(isset($this->innerMap[($tok)]))
		    		{
		    			$i = $this->innerMap[$tok];
		    			$i++;
		    			$this->innerMap[$tok] = $i;
		    		}
		    		else
		    		{
		    			$this->innerMap[$tok] = 1;
		    		}
	    		}
	    		$tok = strtok($this->delimiter);
		}			
    	}
    	catch(Exception $e)
    	{
		echo 'Caught exception tokenizing text: ',  $e->getMessage(), "\n";
		exit;
	}
    }

    function printMap()
    {
    	print_r($this->innerMap);
    	echo "** WordMapCount: "; 
    	echo count($this->innerMap);
    	echo "\n"; 
    }
}


