<?php
return call_user_func(function() {
	return function($object) {
		foreach ($object as $property => $property_value) {
			if ($property_value instanceof Closure) {
				$object->$property = $property_value->bindTo($object, $object);
			}
		}

		return $object;
	};
});