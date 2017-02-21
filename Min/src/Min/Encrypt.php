<?php
namespace Min;

use Min\MinException as MinException;

class Encrypt 
{
  
	public function encrypt($text = '', $method = 'basic') 
	{
		return _encrypt_decrypt('encrypt', $text, $method);
	}


	public function decrypt($text = '', $method = 'basic') 
	{
		return _encrypt_decrypt('decrypt', $text, $method);
	}
	
	private function _encrypt_decrypt($op = 'encrypt', $text = '', $method = NULL) 
	{
		$encryption_array = [];
		$processed = '';

		if ($text === '') {
			return $processed;
		}

		if ($op !== 'encrypt') {
			$op = 'decrypt';
		}

		if ($op == 'decrypt') {
			$encryption_array = @unserialize($text);
		 }

		$key =  $this->key;
		$function = 'encrypt_encryption_methods_' .$method;
		$processed =  $this->{$function}($op, $text);

		// Check for returned value.
		if (!empty($processed) && $op == 'encrypt') {
			$encryption_array = array(
			'text' => $processed,
			'method' => $method,
			);
			// Serialize array.
			$processed = serialize($encryption_array);
		}

		return $processed;
	}
	
	/**
	 * Callback for Encrypt implementation: default.
	 *
	 * This method uses a simple encryption method of character replacement.
	 */
	 
	private function encrypt_encryption_methods_basic($op, $text, $key, $options = []) 
	{
		$processed_text = '';

		// Caching length operations to speed up for loops.
		$text_length = strlen($text);
		$key_length = strlen($key);

		// Loop through each character.
		for ($i = 0; $i < $text_length; $i++) {
			$char = substr($text, $i, 1);
			$keychar = substr($key, ($i % $key_length) - 1, 1);
			// Encrypt or decrypt the character.
			if ($op == 'decrypt') {
			  $char = chr(ord($char) - ord($keychar));
			}
			else {
			  $char = chr(ord($char) + ord($keychar));
			}
			$processed_text .= $char;
		}

		return $processed_text;
	}
	
	
	private function _encrypt_encryption_methods_mcrypt_aes_cbc($op, $text) 
	{
		if (!function_exists('mcrypt_encrypt')) {
			throw new MinException('Mcrypt library not installed.');
		}
		// Open the Mcrypt handle.
		$mcrypt = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		$iv_size = mcrypt_enc_get_iv_size($mcrypt);
		$block_size = mcrypt_enc_get_block_size($mcrypt);
		$hash_function = 'sha256';
		$allowed_key_sizes = array(16, 24, 32);
		$key = $this->key;
		$key_size = strlen($key);
		$salt_size = 32;
		$hmac_size = 32;

		// If the key is not the right size, report an error.
		if (empty($key) || !in_array($key_size, $allowed_key_sizes)) {
			$t_args = array(
			  '@action' => ($op == 'decrypt') ? t('Decryption') : t('Encryption'),
			);
			throw new MinException(t('@action failed because the key is not the right size.', $t_args));
		}

		if ($op == 'decrypt') {
		 
			$text = base64_decode($text);
		 
			// Extract the HMAC.
			$hmac = substr($text, 0, $hmac_size);
			$text = substr($text, $hmac_size);

			// Extract the salt, using half for encryption and
			// half for authentication.
			$salt = substr($text, 0, $salt_size);
			$esalt = substr($salt, 0, strlen($salt) / 2);
			$asalt = substr($salt, strlen($salt) / 2);

			// Generate the authentication subkey.
			$akey = $this->_encrypt_encryption_methods_mcrypt_aes_cbc_hkdf($hash_function, $key, $key_size, $asalt);

			// Calculate the HMAC.
			$calculated_hmac = $this->_encrypt_encryption_methods_mcrypt_aes_cbc_hkdf($hash_function, $text, $hmac_size, $akey);

			// If the HMAC cannot be validated, throw an exception.
			if ($calculated_hmac != $hmac) {
				throw new MinException(t('Decryption failed because the HMAC could not be validated.'));
			}

			$text = substr($text, $salt_size);

			// Get the IV and remove it from the encrypted data.
			$iv = substr($text, 0, $iv_size);
			$text = substr($text, $iv_size);

			// Generate the encryption subkey.
			$ekey = $this->_encrypt_encryption_methods_mcrypt_aes_cbc_hkdf($hash_function, $key, $key_size, $esalt);

			// Initialize for decryption.
			mcrypt_generic_init($mcrypt, $ekey, $iv);

			// Decrypt the data.
			$processed_text = mdecrypt_generic($mcrypt, $text);

			// Terminate decryption.
			mcrypt_generic_deinit($mcrypt);

			// Remove any padding.
			$pad = ord($processed_text[strlen($processed_text) - 1]);
			$processed_text = substr($processed_text, 0, -$pad);

			// Close the Mcrypt handle.
			mcrypt_module_close($mcrypt);
		}
		else {
			// Create a random IV.
			$iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);

			// Generate a random 32-byte salt, using half for encryption and
			// half for authentication.
			$salt =  random_bytes($salt_size);
			$esalt = substr($salt, 0, strlen($salt) / 2);
			$asalt = substr($salt, strlen($salt) / 2);

			// Generate a subkey for encryption.
			$ekey = $this->_encrypt_encryption_methods_mcrypt_aes_cbc_hkdf($hash_function, $key, $key_size, $esalt);

			// Initialize for encryption.
			mcrypt_generic_init($mcrypt, $ekey, $iv);

			// Determine the necessary padding amount.
			$pad = $block_size - (strlen($text) % $block_size);

			// Encrypt the text.
			$processed_text = mcrypt_generic($mcrypt, $text . str_repeat(chr($pad), $pad));

			// Prepend the encrypted data with the salt and IV.
			$processed_text = $salt . $iv . $processed_text;

			// Generate a subkey to use as a salt for the HMAC.
			$akey = $this->_encrypt_encryption_methods_mcrypt_aes_cbc_hkdf($hash_function, $key, $key_size, $asalt);

			// Calculate the HMAC and prepend it to the processed data.
			$hmac = $this->_encrypt_encryption_methods_mcrypt_aes_cbc_hkdf($hash_function, $processed_text, $hmac_size, $akey);
			$processed_text = base64_encode($hmac . $processed_text);
		 

			mcrypt_module_close($mcrypt);
		}

		return $processed_text;
	}

	/**
	 * Generate a hash.
	 *
	 * @param string $hash_function Hash function
	 * @param string $ikm Initial keying material
	 * @param int $length Length of the key in bytes
	 * @param string $salt Salt
	 * @return string $key The generated key
	 */
	private function _encrypt_encryption_methods_mcrypt_aes_cbc_hkdf($hash_function, $ikm, $length, $salt) 
	{
	  $key = hash_hmac($hash_function, $ikm, $salt, TRUE);
	  $key = substr($key, 0, $length);
	  return $key;
	}
	
	/**
	 * Callback for Encrypt implementation: Mcrypt Rijndael 256.
	 *
	 * This method uses PHP's Mcrypt extension and Rijndael 256. Base64 encoding is
	 * used by default, unless disabled by setting 'base64' to FALSE in $options.
	 */
	 
	private function _encrypt_encryption_methods_mcrypt_rij_256($op, $text) 
	{
		$processed_text = '';

		// Key cannot be too long for this encryption.
		$key = substr($this->key, 0, 32);

		// Define iv cipher.
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		if ($op == 'decrypt') { 
			$text = base64_decode($text);
			return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv));
		}
		else {
			$processed_text = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv);
			$processed_text = base64_encode($processed_text);
		}
		return $processed_text;
	  }
	  
	/**
	 * Callback for Encrypt implementation: phpseclib.
	 *
	 * This method uses the PHP Secure Communications Library and AES-256.
	 * Base64 encoding is used by default, unless disabled by setting
	 * 'base64' to FALSE in $options.
	 */
	 
	private function _encrypt_encryption_methods_phpseclib($op, $text, $key, $options = array()) {
		$processed_text = '';
		if ($op == 'decrypt') {
			$text = base64_decode($text);
		}
		if ($path = vendor('phpseclib')) {
			require_once $path . '/Crypt/AES.php';
			$aes = new \Crypt_AES();
			$aes->setKey($key);
			$processed_text = $aes->{$op}($text);
		}
		if ($op == 'encrypt') {
			$processed_text = base64_encode($processed_text);
		}

		return trim($processed_text);
	}

}

