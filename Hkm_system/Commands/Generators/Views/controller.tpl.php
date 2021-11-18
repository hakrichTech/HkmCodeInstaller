<@php

namespace {namespace};

use {useStatement};

class {class} extends {extends}
{
<?php if ($type === 'controller'): ?>
	/**
	 * Return an array of resource objects, themselves in array format
	 *
	 * @return mixed
	 */
	public static function INDEX()
	{
		//
	}

	/**
	 * Return the properties of a resource object
	 *
	 * @return mixed
	 */
	public static function SHOW($id = null)
	{
		//
	}

	/**
	 * Return a new resource object, with default properties
	 *
	 * @return mixed
	 */
	public static function NEW()
	{
		//
	}

	/**
	 * Create a new resource object, from "posted" parameters
	 *
	 * @return mixed
	 */
	public static function CREATE()
	{
		//
	}

	/**
	 * Return the editable properties of a resource object
	 *
	 * @return mixed
	 */
	public static function EDIT($id = null)
	{
		//
	}

	/**
	 * Add or update a model resource, from "posted" properties
	 *
	 * @return mixed
	 */
	public static function UPDATE($id = null)
	{
		//
	}

	/**
	 * Delete the designated resource object from the model
	 *
	 * @return mixed
	 */
	public static function DELETE($id = null)
	{
		//
	}
<?php elseif ($type === 'presenter'): ?>
	/**
	 * Present a view of resource objects
	 *
	 * @return mixed
	 */
	public static function INDEX()
	{
		//
	}

	/**
	 * Present a view to present a specific resource object
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public static function SHOW($id = null)
	{
		//
	}

	/**
	 * Present a view to present a new single resource object
	 *
	 * @return mixed
	 */
	public static function NEW()
	{
		//
	}

	/**
	 * Process the creation/insertion of a new resource object.
	 * This should be a POST.
	 *
	 * @return mixed
	 */
	public static function CREATE()
	{
		//
	}

	/**
	 * Present a view to edit the properties of a specific resource object
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public static function EDIT($id = null)
	{
		//
	}

	/**
	 * Process the updating, full or partial, of a specific resource object.
	 * This should be a POST.
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public static function UPDATE($id = null)
	{
		//
	}

	/**
	 * Present a view to confirm the deletion of a specific resource object
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public static function REMOVE($id = null)
	{
		//
	}

	/**
	 * Process the deletion of a specific resource object
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public static function DELETE($id = null)
	{
		//
	}
<?php else: ?>
	public static function INDEX()
	{
		//
	}
<?php endif ?>
}
