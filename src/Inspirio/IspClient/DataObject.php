<?php
namespace Inspirio\IspClient;

class DataObject
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		foreach ($this as $property => $value) {
			$this->$property = null;
		}
	}

	/**
	 * Converts the string to a camel case
	 *
	 * @param string $str
	 * @return string
	 */
	private static function toCamel($str)
	{
		return preg_replace_callback('/_([a-z])/', function($str) {
			return strtoupper($str[1]);
		}, $str);
	}

	/**
	 * Converts the string to a underscore form
	 *
	 * @param string $str
	 * @return string
	 */
	private static function toUnderscore($str)
	{
		return preg_replace_callback('/([A-Z])/', function($str) {
			return '_'.strtolower($str[1]);
		}, $str);
	}

	/**
	 * Creates an object instance from the received data
	 *
	 * @param array $data
	 * @return IspDataObject
	 */
	public static function fromData(array $data)
	{
		$object = new static();

		foreach ($data as $property => $value) {
			$property = self::toCamel($property);

			$object->$property = $value;
		}

		return $object;
	}

	/**
	 * Exports the object internal data
	 *
	 * @return array
	 */
	public function toData()
	{
		$data = array();

		foreach ($this as $property => $value) {
			if ($value === null) {
				continue;
			}

			$property = self::toUnderscore($property);

			$data[$property] = $value;
		}

		return $data;
	}

	/**
	 * Applies the default valus on the NULL properties
	 *
	 * @param array $defaults
	 */
	public function applyDefaultValues(array $defaults)
	{
		foreach ($defaults as $property => $value) {

			if ($this->$property === null) {
				$this->$property = $value;
			}
		}
	}
}
