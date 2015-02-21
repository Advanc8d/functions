<?php
if (!function_exists('for_all')) {
	/**
	 * Применение callback функции к каждому элементу массива, который является строкой или просто строке
	 * Всем переменным не являющимися массивом или строкой, будет присвоено значение null
	 *
	 * @param  mixed $data Строка или массив с данными
	 * @param  Closure $callback callback функция
	 * @return mixed
	 */
	function for_all($data, Closure $callback)
	{
		switch (true) {
			case is_array($data): {
				foreach ($data as &$val) {
					$val = for_all($val, $callback);
				}
				break;
			}
			case is_scalar($data): {
				$data = $callback($data);
				break;
			}
			default: {
			$data = null;
			}
		}
		return $data;
	}
}

if (!function_exists('get_key')) {
	/**
	 * Получение значения по ключу из массива, либо возврат значения по умолчанию
	 *
	 * @param mixed $data массив
	 * @param string $key ключ массива
	 * @param mixed $default null значение по умолчанию
	 * @param Closure $validate null функция дополнительной валидации значения (должна возвращать true или false)
	 * @return mixed
	 */
	function get_key($data, $key, $default = null, $validate = null)
	{
		$out = $default;
		if (is_array($data) && (is_int($key) || is_string($key)) && $key !== '' && array_key_exists($key, $data)) {
			$out = $data[$key];
		}
		if (!empty($validate) && is_callable($validate)) {
			$out = (($validate($out) === true) ? $out : $default);
		}
		return $out;
	}
}

if (!function_exists('rename_key_array')) {
	/**
	 * Переменовывание элементов массива
	 *
	 * @param array $data массив с данными
	 * @param string $prefix префикс ключей
	 * @param string $suffix суффикс ключей
	 * @param string $addPS разделитель суффиксов, префиксов и ключей массива
	 * @param string $sep разделитель ключей при склейке многомерных массивов
	 * @return array массив с переименованными ключами
	 */
	function rename_key_array($data, $prefix = '', $suffix = '', $addPS = '.', $sep = '.')
	{
		$out = array();
		if (is_array($data)) {
			if (!is_scalar($addPS)) {
				$addPS = '';
			}
			$InsertPrefix = (is_scalar($prefix) && $prefix !== '') ? ($prefix . $addPS) : '';
			$InsertSuffix = (is_scalar($suffix) && $suffix !== '') ? ($addPS . $suffix) : '';
			foreach ($data as $key => $item) {
				$key = $InsertPrefix . $key;
				$val = null;
				switch (true) {
					case is_scalar($item): {
						$val = $item;
						break;
					}
					case is_array($item): {
						$val = (is_scalar($sep) && $sep !== '') ? rename_key_array($item, $key . $sep, $InsertSuffix, '', $sep) : array();
						$out = array_merge($out, $val);
						$val = '';
						break;
					}
				}
				$out[$key . $InsertSuffix] = $val;
			}
		}
		return $out;
	}
}

if (!function_exists('make_array')) {
	/**
	 * Создание многомерного массива из двухмерного, в имени которого содержится разделитель
	 *
	 * @param array $data массив с данными
	 * @param string $sep разделитель ключей при склейке многомерных массивов
	 * @return array массив с переименованными ключами
	 */
	function make_array($data, $sep = '.')
	{
		$out = array();
		if (is_array($data)) {
			if (is_scalar($sep) && $sep !== '') {
				foreach ($data as $key => $val) {
					$keys = explode($sep, $key);
					$workArray = &$out;
					foreach ($keys as $i => $subKey) {
						if (!array_key_exists($subKey, $workArray)) {
							$workArray[$subKey] = ($i + 1 == count($keys)) ? $val : array();
						}
						$workArray = &$workArray[$subKey];
					}
				}
			} else {
				$out = $data;
			}
		}
		return $out;
	}
}

if (!function_exists('array_sort')) {
	/**
	 * Сортировка массива
	 *
	 * @param array $arr массив с данными
	 * @param string $sort_field по какому ключу сортировать массив
	 * @param bool $desc направление сортировки
	 * @return array
	 */
	function array_sort(array $arr, $sort_field, $desc = false)
	{
		$first = reset($arr);
		if (!isset($first[$sort_field])) {
			return $arr;
		}
		foreach ($arr as $key => $item) {
			$sort[$key] = $item[$sort_field];
		}
		array_multisort($sort, $desc ? SORT_DESC : SORT_ASC, $arr);
		return $arr;
	}
}

if (!function_exists('array_unset')) {
	/**
	 * Удаление списка ключей из массива
	 *
	 * @param array $arr массив с данными
	 * @param string|array $keys ключ или массив ключей, которые необходимо удалить
	 * @return array
	 */
	function array_unset($arr, $keys)
	{
		if (is_scalar($keys)) {
			$keys = array($keys);
		}
		if (is_array($keys) && is_array($arr)) {
			foreach ($keys as $key) {
				if (is_scalar($key)) {
					unset($arr[$key]);
				}
			}
		}
		return $arr;
	}
}

if (!function_exists('array_filter')) {
	/**
	 * Фильтрация массива
	 *
	 * @param array $data массив с данными
	 * @param string|Closure $filter функция для фильтрации массива
	 * @return array
	 */
	function array_filter($data, $filter)
	{
		$out = array();
		foreach ($data as $k => $v) {
			if (is_callable($filter) && $filter($v)){
				$out[$k] = $v;
			}
		}
		return $out;
	}
}

if (!function_exists('array_path')) {
	/**
	 * Получение значения многомерного массива
	 *
	 * @param array $array многомерный массив
	 * @param string $path путь к ключу многомерного массива (каждый уровень разделяется символом $separator)
	 * @param mixed $default значение по умолчанию, если ключа в массиве нет
	 * @param string $separator разделитель уровней массива
	 * @return mixed
	 */
	function array_path($array, $path, $default = null, $separator = '.')
	{
		$path = explode($separator, $path);
		while ($key = array_shift($path)) {
			if (!isset($array[$key])){
				return $default;
			}
			$array = $array[$key];
		}
		return $array;
	}
}

if (!function_exists('array_path_unset')) {
	/**
	 * Удаление ключа многомерного массива
	 *
	 * @param array $array многомерный массив
	 * @param string $path путь к ключу многомерного массива (каждый уровень разделяется символом $separator)
	 * @param string $separator разделитель уровней массива
	 * @return void
	 */
	function array_path_unset(&$array, $path, $separator = '.')
	{
		$tmp = &$array;
		$path = explode($separator, $path);
		while (count($path) > 1) {
			$key = array_shift($path);
			if (!isset($tmp[$key])) return;
			$tmp = &$tmp[$key];
		}
		unset($tmp[array_shift($path)]);
	}
}

if (!function_exists('array_path_replace')) {
	/**
	 * Заменить значение многомерного массива
	 *
	 * @param array $array многомерный массив
	 * @param string $path путь к ключу многомерного массива (каждый уровень разделяется символом $separator)
	 * @param mixed $value новое значение
	 * @param string $separator разделитель уровней массива
	 * @return void
	 */
	function array_path_replace(&$array, $path, $value, $separator = '.')
	{
		$tmp = &$array;
		$path = explode($separator, $path);
		while (count($path) > 1) {
			$key = array_shift($path);
			if (!isset($tmp[$key])) $tmp[$key] = array();
			$tmp = &$tmp[$key];
		}
		$tmp[array_shift($path)] = $value;
	}
}

if (!function_exists('array_remove_blanks')) {
	/**
	 * Удалить пустые элементы из массива
	 *
	 * @param array $array
	 * @param array $symbols удаляемые значения
	 * @return array
	 */
	function array_clean($array, array $symbols = array('', null))
	{
		return is_array($array) ? array_diff($array, $symbols) : array();
	}
}

if( !function_exists('array_shuffle')){
	/**
	 * Перемешать массив в случайном порядке с сохранением ключей
	 *
	 * @param array $data массив с данными
	 * @return bool результат сортировки массива
	 */
	function array_shuffle(array &$data = array()){
		return uksort($data, function() { return rand() > rand(); });
	}
}

if (!function_exists('array_random')) {
	/**
	 * Получить несколько случайных записей из массива с сохранением ключей
	 *
	 * @param array $data массив с данными
	 * @param int $count требуемое число записей из массива
	 * @return array|mixed
	 */
	function array_random(array $data = array(), $count = 1)
	{
		$flag = array_shuffle($data);
		if ($flag) {
			if ((int)$count > 0) {
				$data = current(array_chunk($data, (int)$count, true));
			}
		} else {
			$data = array();
		}
		return $data;
	}
}

if(!function_exists('is_assoc')){
	/**
	 * Является ли массив ассоциативным
	 *
	 * @param array $array проверяемый массив
	 * @return bool результат проверки
	 */
	function is_assoc($array) {
		 	return is_array($array) ? (bool)count(array_filter(array_keys($array), 'is_string')) : false;
	}
}