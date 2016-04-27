<?
	global $show_class_methods;

	function error($var, $str_left="", $str_right="") {
	
		if(is_null($var)) {
			$var = $str_left;
			$str_left = "";
			$str_right = "";
		}
	
		$error = var_export($var, true);
		$error = $str_left . $error . $str_right . "\n";
		error_log($error, 3 ,'C:\logs\dnate_errors.log');
	}

	if(!function_exists('file_put_contents')) {
		function file_put_contents($file, $data) {
			$handle = fopen($file, 'w');

			switch(gettype($data)) {
				case 'array':
					foreach($data as $line) {
						fwrite($handle, $line . "\n");
					}
					break;
				case 'string':
				case 'integer':
				case 'boolean':
				case 'double':
				case 'null':
					fwrite($handle, $data);
					break;
				default:
					fwrite($handle, gettype($data));
			}
		}
	}

	function clearLog($log = "C:\logs\dnate_dbo.log") {
		file_put_contents($log, "");
	}
	/**
	 * This method keeps strings and arrays in the same debug file. useful for seeing things in the order they happen.
	 *
	 * @param $var Mixed
	 * @param $mode String
	 * @param $indent String
	 * @author dnate
	 * @since 2012.05.04
	 */
	function debugOut($var, $mode = "a", $indent = "\n") {
		if( is_array($var) || is_object($var) ) {
			pre($var, $mode, 'C:/logs/dnate_dbo.log', $indent);
		}
		else {
			writeToFile($var, $mode, 'C:/logs/dnate_dbo.log', $indent);
		}
	}

	/**
	 * makes getting a backtrace a little easier, still needs some work.
	 * 
	 * @param boolean (optional)$print_to_screen  default false
	 * @author dnate
	 * @since 2013.08.15
	 */
	function dnateBacktrace($print_to_screen = false) {
		$backtrace = debug_backtrace();
		$message   = array();
		$i         = 0;

		foreach($backtrace as $dbb) {
			$message[$i]['class']    = $dbb['class'];
			$message[$i]['function'] = $dbb['function'];
			$message[$i]['args']     = $dbb['args'];
			$message[$i]['file']     = $dbb['file'];
			$message[$i]['line']     = $dbb['line'];
			$i++;
		}

		if($print_to_screen !== false) {
			echo "<pre>".print_r($message, true)."</pre>";
		}

		dbo_arr('backtrace', $message);
	}

	/**
	 * shortcut for debugOut, doesn't accept the indent argument.
	 *
	 * @param $var Mixed
	 * @param $mode String
	 * @author dnate
	 * @since 2012.05.04
	 */
	function dbo($var, $mode = "a", $recurse = false) {
		if( is_array($var) ) {
			dbo_arr("", $var, $mode);
		} else if(is_object($var)) {
			dbo(get_class($var) . " Object ");
			dbo_obj($var, "\n\t\t");//for some weird reason this isn't working even though when an object is in an arry it does work.
		} else {
			debugOut($var, $mode);
		}
	}

	function showClassMethods() {
		global $show_class_methods;
		$show_class_methods = true;
	}

	function unShowClassMethods() {
		global $show_class_methods;
		$show_class_methods = false;
	}
	/**
	 * this method displays the class methods and # of arguments those methods accept
	 * 
	 * @param object $class
	 * @param string $indent
	 * 
	 */
	function dbo_obj($class, $indent = "\n") {
		$txt = "";

		$version = phpversion();
		if(preg_match("/^5\.3\.[\d]+/", $version) ) {
			php5Object($class, $txt, $indent);
		} else {
			php4Object($class, $txt);
		}

		return $txt;
	}

	/**
	 * Allows you to apply a label to your array so you can keep track of various arrays, doesn't accept the indent argument.
	 *
	 * @param $var Mixed
	 * @param $mode String
	 * @author dnate
	 * @since 2012.05.04
	 */
	function dbo_arr($var, $array, $mode = "a") {
		debugOut($var, $mode, "\n\n");
		debugOut($array, $mode, "\n\t\t\t\t\t\t");
	}

	/**
	 * Spits out your array/object in a file with indented formated
	 * @param $array
	 * @param $mode
	 * @param $indent
	 * @author dnate
	 */
	function pre(&$array, $mode = 'w', $filename = "C:\logs\dnate_arr.log", $indent = "\r\n") {
		if(is_array($array) || is_object($array)) {
			if($mode == 'echo') {
				preprint_r($array);
			} else {
				$handle = fopen($filename, $mode);
				fwrite($handle, getArrayKeysAndValues($array, $indent."Array (", $indent));
				fwrite($handle, "\n");
				fclose($handle);
			}
		} else {
			writeToFile($array);
		}
	}

	function preprint_r($array) {
		echo "<pre>".print_r($array, true)."</pre>";
	}

	/**
	 * adds a label to the output (for identifying what output you are looking at)
	 */
	function label($label, $file = "dbo", $mode = 'a') {
		switch($file) {
			case "dump":
				$file = 'C:\logs\dnate_arr.log';
				break;

			case "dbo":
				$file = 'C:\logs\dnate_dbo.log';
				break;
		}

		writeToFile($label, $mode, $file);
	}

	/**
	 * Spits out your array/object in a file with indented formated, good for private and public parameters
	 * needs some work, output is not as pretty as pre()
	 * @param $var
	 * @param $mode
	 * @param $file
	 * @author dnate
	 */
	function errDump($var, $mode = 'w', $file = 'C:\logs\dnate_arr.log') {
		ob_start();

		var_dump($var);

		$output = strip_tags(str_replace("&gt;", ">", ob_get_contents()));
		wtf($output, $mode, $file);
		ob_end_flush();
	}

	/**
	 * kills the script and echo's the var (in &lt;pre&gt; tags for array or object)
	 * @param $var
	 * @author dnate
	 */
	function kill($var) {
		if(is_array($var) || is_object($var)) {
			$die = "<pre>".print_r($var,true)."</pre>";
			die($die);
		} else {
			die($var);
		}
	}

	/**
	 * keeps the script alive and echo's the var (in &lt;pre&gt; tags for array or object)
	 * @param $var
	 * @author dnate
	 */
	function survive($var) {
		if(is_array($var) || is_object($var)) {
			$die = "<pre>".print_r($var,true)."</pre>";
			echo $die;
		} else {
			echo $var;
		}
	}

	/**
	 * writes (appends (usually)) the var to a file
	 * @param $string
	 * @param $filename
	 * @param $mode
	 * @param $indent
	 * @author dnate
	 */
	function writeToFile($string, $mode = 'a', $filename = 'C:\logs\dnate_dbo.log', $indent = "\n") {
		if(is_array($string)) {
			if($filename == "C:\logs\dnate_dbo.log") {
				$filename = "C:\logs\dnate_arr.log";
			}
			pre($string, $mode, $filename, $indent);
		}
		if(is_writable($filename)) {
			$handle = fopen($filename, $mode);
			$time = date('Y-m-d H:i:s');
			fwrite($handle, $indent.$time." - ".$string);
			fclose($handle);
		} else {
			echo "The file {$filename} is not writeable";
		}
	}

	/**
	* an hilarious alias for writeToFile
	* @param	$string
	* @param $filename
	* @param $mode
	* @param $indent
	* @author dnate
	*/
	function wtf($string, $mode = 'a', $filename = 'C:\logs\dnate_dbo.log', $indent = "\n") {
		writeToFile($string, $mode, $filename, $indent);
	}

	function errUnserialize($string = "") {
		$string = "O%3A10%3A%22W1_Message%22%3A17%3A%7Bs%3A22%3A%22%00W1_Message%00class_name%22%3Bs%3A15%3A%22EssdrPaymentCTL%22%3Bs%3A21%3A%22%00W1_Message%00data_blob%22%3BN%3Bs%3A21%3A%22%00W1_Message%00file_name%22%3BN%3Bs%3A25%3A%22%00W1_Message%00function_name%22%3BN%3Bs%3A16%3A%22%00W1_Message%00line%22%3BN%3Bs%3A19%3A%22%00W1_Message%00message%22%3Bs%3A22%3A%22An+error+has+occurred.%22%3Bs%3A20%3A%22%00W1_Message%00priority%22%3BN%3Bs%3A27%3A%22%00W1_Message%00priority_number%22%3Bi%3A2%3Bs%3A25%3A%22%00W1_Message%00priority_text%22%3Bs%3A8%3A%22CRITICAL%22%3Bs%3A38%3A%22%00W1_Message%00serialized_backtrace_array%22%3Bs%3A871%3A%22s%3A862%3A%22%230++W1_Message-%3EsetSerializedBacktraceArray%28AGHU%29+called+at+%5B%2Fwebdata%2FW1%2FW1_System%2FW1_Messaging%2FW1_Message.php%3A121%5D%0A%231++W1_Message-%3E__construct%28An+error+has+occurred.%2C+CRITICAL%2C+EssdrPaymentCTL%2C+%2C+AGHU%29+called+at+%5B%2Fwebdata%2FW1%2FW1_System%2FW1_Messaging%2FW1_MessageSystem.php%3A263%5D%0A%232++W1_MessageSystem-%3ElogMessage%28An+error+has+occurred.%2C+CRITICAL%29+called+at+%5B%2Fwebdata%2FW1%2FW1_Services%2FW1_PublicCTL%2FApplicationCTL%2FEssdrCTL%2FEssdrBaseCTL.php%3A1210%5D%0A%233++EssdrBaseCTL-%3ErenderTemplate%28%29+called+at+%5B%2Fwebdata%2FW1%2FW1_System%2FW1_Controller%2FW1_ViewController.php%3A115%5D%0A%234++W1_ViewController-%3EexecuteService%28%29+called+at+%5B%2Fwebdata%2FW1%2FW1_System%2FW1_Controller%2FW1_SiteConstructor.php%3A434%5D%0A%235++W1_SiteConstructor-%3EexecuteService%28%29+called+at+%5B%2Fwebdata%2FW1%2FW1_System%2FW1_Controller%2FW1_SiteConstructor.php%3A306%5D%0A%236++W1_SiteConstructor-%3Eexecute%28%29+called+at+%5B%2Fwebdata%2FW1%2FW1_DocRoot%2FJ1%2Findex.php%3A20%5D%0A%22%3B%22%3Bs%3A33%3A%22%00W1_Message%00serialized_case_array%22%3BN%3Bs%3A35%3A%22%00W1_Message%00serialized_cookie_array%22%3BN%3Bs%3A32%3A%22%00W1_Message%00serialized_get_array%22%3BN%3Bs%3A33%3A%22%00W1_Message%00serialized_post_array%22%3BN%3Bs%3A35%3A%22%00W1_Message%00serialized_server_array%22%3BN%3Bs%3A36%3A%22%00W1_Message%00serialized_session_array%22%3BN%3Bs%3A21%3A%22%00W1_Message%00timestamp%22%3BN%3B%7D";
		// $string = preg_replace("/[AO0-9%]{2,}/", " ", $string);
		// $string = unserialize($string);
		$string = urldecode($string);
		// $string = unserialize($string);
		// errDump($string);
		return $string;
	}

	function php4Object(&$var, &$txt) {
		dbo("using version 4 object parser");
		$var = get_object_vars($var);
		$txt = str_replace("Array", "Object", $txt);
	}

	function php5Object($class, &$txt, $indent = "\n") {
		global $show_class_methods;
		//showing methods can get really intense because it shows methods of parent classes as well as the $class object
		//only show methods if you absolutley need to.
		if($show_class_methods === true) {
			$reflection = new ReflectionClass($class);
			$methods    = $reflection->getMethods();
			sort($methods);
			foreach($methods as $method) {
				$txt .= $indent."\t" . $method->getDeclaringClass()->getName() .'::'. $method->getName() . " (";
				if($method->isPrivate()) {
					$txt .= $indent."\t\tis Private";
				} else if($method->isProtected()) {
					$txt .= $indent."\t\tis Protected";
				} else if($method->isPublic()) {
					$txt .= $indent."\t\tis Public";
				}
				
				if($method->isFinal()) {
					$txt .= $indent."\t\tis Final";
				}
				if($method->isStatic()) {
					$txt .= $indent."\t\tis Static";
				}
				if($method->isAbstract()) {
					$txt .= $indent."\t\tis Abstract";
				}
				
				$txt .= $indent."\t\taccepts " . $method->getNumberOfParameters() . " arguments (" . $method->getNumberOfRequiredParameters() . " required).";
				$txt .= $indent . "\t)";
			}
		}
	}

	/**
	 * drills through arrays and creates indented output
	 * @param $array
	 * @param $indent
	 * @return string
	 * @author dnate
	 */
	function getArrayKeysAndValues($array, $txt = '', $indent = "\r\n\t") {
		if( empty($txt) ) {
			if(is_object($array)) {
				$classname = get_class($array);
				$txt .= " =>" . $indent."\t" . $classname . " Object (";
			}
			else {
				$txt .= " => Array (";
			}
		}
		if( is_object($array) ) {
			$txt .= dbo_obj($array, $indent . "\t\t");
			$txt .= $indent . "\t)";
		}
		if(is_array($array)) {
			foreach($array as $k => $v) {
				$txt .= $indent."\t[".$k."]";
				if( is_array($v) || is_object($v) ) {
					$txt .= getArrayKeysAndValues($v, '', $indent."\t");
				} else {
					$type = '('.gettype($v).') ';
					if(is_bool($v)) {
						$txt .= $type.$v;
					} else {
						if(is_String($v) || is_numeric($v)) {
							if( trim($v) == '' ) {
								$txt .= " => '".$type.$v."'";
							} else {
								$txt .= " => ".$type.$v;
							}
						} else {
							$txt .= " => don't know what to do with variable of type ".gettype($v);
						}
					}
				}
			}
			$txt .= $indent.")";
		}
		return $txt;
	}

	function showHash($input, $hash = "sha1", $show = true) {
		switch($hash) {
			case "crc32":
				$output = sprintf("%u", crc32($input));
				break;
			case "sha1":
				$output = sha1($input);
				break;
			case "sha1_file":
				if(file_exists($input)) {
					$output = sha1_file($input);
				} else {
					$output = "unable to open file, perhaps it doesn't exist";
				}
				break;
			case "md5_file":
				if(file_exists($input)) {
					$output = md5_file($input);
				} else {
					$output = "unable to open file, perhaps it doesn't exist";
				}
				break;
			case "md5":
				$output = md5($input);
				break;
			default:
				$output = $input;
		}
		if($show) {
			return $output;
		} else {
			writeToFile($input." = ".$output);
		}
	}

	function hashMatch($base, $hash = NULL, $hash_type = "md5") {
		// set_time_limit(300);//turn off time limit this can take a while.
		$variations = array(); //container for different variations of $base

		// split $base into array
		// $base_chars = str_split($base);//use this for PHP5
		for($i = 0; $i < strlen($base); $i++) { //use this for PHP4
			$base_chars[] = substr($base, $i, 1);
		}

		//get letter possibilities for each letter in $base
		foreach($base_chars as $char) {
			$letters[] = findPossibleChars($char);
			$cnt[] = 0;
		}

		//count backward from last letter to limit iterations
		$decrimenter = count($letters) - 1;

		//determine total # of possibilities
		$total_possible = 1;
		foreach($letters as $letter) {
			$total_possible = $total_possible * count($letter);
		}
		
		$i = 0;
		$possibility = null;
		$switch      = null;

		while($i < $total_possible) {
			for($z = count($letters) -1; $z >= 0; $z--) {
				$q = $z - 1;
			
				// last letter always loops
				if( $z == count($letters) - 1) {
					$possibility = $letters[$z][$cnt[$z]];
					// increment our counter
					if( $cnt[$z] == count($letters[$z]) - 1 ) {
						$cnt[$z] = 0;
						$switch[$q] = TRUE;
					} else {
						$cnt[$z]++;
						$switch[$q] = FALSE;
					}
				} else {
					$possibility = $letters[$z][$cnt[$z]].$possibility;
					if($switch[$z]) {
						// increment our counter
						if( $cnt[$z] == count($letters[$z]) - 1 ) {
							$cnt[$z] = 0;
							if($z > 0) {
								$switch[$q] = TRUE;
							}
						} else {
							$cnt[$z]++;
							if($z > 0) {
								$switch[$q] = FALSE;
							}
						}
						$switch[$z] = FALSE;
					}
				}
			}

			if( empty($hash) ) {
				if( !in_array($possibility, $variations) ) {
					$variations[] = $possibility;
					$i++;
				}
			} else {
				if( showHash($possibility, $hash_type) === $hash ) {
					echo "$possibility = $hash <br />";
					$ii++;
					echo "$ii of $total_possible tries to find match";
					break;
				}
				$i++;
			}
		}

		if( empty($hash) ) {
			echo "$total_possible total possible variations";
			echo "<br />";
			$i = 0;
			foreach($variations as $variation) {
				$variations[$i] = $variations[$i].' = '.showHash($variations[$i], $hash_type);
				$i++;
			}
			survive($variations);
		}
	}

	function findPossibleChars($char) {
		if( is_letter($char) ) {
			$char = strtolower($char);
		}
		switch($char) {
			case "0":
				$letters = array("0", "o", "O");
				break;
			case "1":
				$letters = array("1", "i", "I", "l", "!");
				break;
			case "2":
				$letters = array($char);
				break;
			case "3":
				$letters = array("3", "E", "m", "M");
				break;
			case "4":
				$letters = array("4", "h");
				break;
			case "5":
				$letters = array("5", "S", "s", "$");
				break;
			case "6":
			case "9":
				$letters = array("6", "9", "g");
				break;
			case "7":
				$letters = array("7", "T");
				break;
			case "8":
				$letters = array($char, strtoupper($char), "8");
				break;
			case "a":
				$letters = array($char, strtoupper($char), "@");
				break;
			case "b":
				$letters = array($char, strtoupper($char), "8");
				break;
			case "e":
			case "m":
				$letters = array($char, strtoupper($char), "3");
				break;
			case "g":
				$letters = array($char, strtoupper($char), "6");
				break;
			case "h":
				$letters = array($char, strtoupper($char), "4");
				break;
			case "i":
			case "l":
				$letters = array($char, strtoupper($char), "1", "!");
				break;
			case "o":
				$letters = array($char, strtoupper($char), "0");
				break;
			case "s":
				$letters = array($char, strtoupper($char), "$", "5");
				break;
			case "t":
				$letters = array($char, strtoupper($char), "7");
				break;
			case "v":
				$letters = array($char, strtoupper($char), "^");
				break;
			default:
				if(is_special($char)) {
					$letters = array($char);
				} else {
					$letters = array($char, strtoupper($char));
				}
		}
		return $letters;
	}

	function is_letter($char) {
		if( strtolower($char) != $char && strtoupper($char) != $char ) {
			return TRUE;
		}
		return FALSE;
	}

	function is_special($char) {
		if(!is_numeric($char) && !is_letter($char)) {
			return FALSE;
		}
		return TRUE;
	}

	if( !function_exists('memory_get_usage') ) {
		function memory_get_usage(){
			$output = array();
			exec('tasklist /FI "PID eq '.getmypid().'" /FO LIST', $output );
			return preg_replace( '/[^0-9]/', '', $output[5] ) * 1024;
		}
	}
?>