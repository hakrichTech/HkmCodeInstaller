<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Validation;

use Hkm_code\HTTP\IncomingRequest;
use Hkm_code\HTTP\Request;
use Hkm_code\Validation\Exceptions\ValidationException;
use Hkm_code\Vezirion\ServicesSystem;
use Hkm_code\View\RendererInterface;
use InvalidArgumentException;
use PhpParser\Node\Expr\Print_;

/**
 * Validator
 */
class Validation implements ValidationInterface
{
	/**
	 * Files to load with validation functions.
	 *
	 * @var array
	 */
	protected static $ruleSetFiles;

	/**
	 * The loaded instances of our validation files.
	 *
	 * @var array
	 */
	protected static $ruleSetInstances = [];

	/**
	 * Stores the actual rules that should
	 * be ran against $data.
	 *
	 * @var array
	 */
	public static $rules = [];

	/**
	 * The data that should be validated,
	 * where 'key' is the alias, with value.
	 *
	 * @var array
	 */
	protected static $data = [];

	/**
	 * Any generated errors during validation.
	 * 'key' is the alias, 'value' is the message.
	 *
	 * @var array
	 */
	protected static $errors = [];

	/**
	 * Stores custom error message to use
	 * during validation. Where 'key' is the alias.
	 *
	 * @var array
	 */
	public static $customErrors = [];

	/**
	 * Our configuration.
	 *
	 * @var ValidationConfig
	 */
	protected static $config;
	public static $thiss;

	/**
	 * The view renderer used to render validation messages.
	 *
	 * @var RendererInterface
	 */
	protected static $view;

	/**
	 * Validation constructor.
	 * 
	 * @param ValidationConfig  $config
	 * @param RendererInterface $view
	 */
	public  function __construct($config,  $view = null)
	{
		self::$thiss=$this;
		self::$ruleSetFiles = $config::$ruleSets;

		self::$config = $config;

		self::$view = $view;
	}


	public static function ADD_RULES_FILES($array_rules)
	{
		if (!empty($array_rules)||!is_null($array_rules)) {
			$array_rules = is_array($array_rules)?$array_rules:[$array_rules];
		    

			$array_rules = count($array_rules)>0?array_map("hkm_get_class",hkm_apply_runtime_filters("valid_class",$array_rules)):[];
			self::$ruleSetFiles = array_merge(self::$ruleSetFiles,$array_rules);
		}
		
		return self::$thiss;
	}

	/**
	 * Runs the validation process, returning true/false determining whether
	 * validation was successful or not.
	 *
	 * @param array|null  $data    The array of data to validate.
	 * @param string|null $group   The predefined group of rules to apply.
	 * @param string|null $dbGroup The database group to use.
	 *
	 * @return boolean
	 */
	public static function RUN(array $data = null, string $group = null, string $dbGroup = null): bool
	{
		$data = $data ?? self::$data;

		
		// i.e. is_unique
		$data['DBGroup'] = $dbGroup;

		self::LOAD_RULE_SETS();

		self::LOAD_RULE_GROUP($group);

		// If no rules exist, we return false to ensure
		// the developer didn't forget to set the rules.
		if (empty(self::$rules))
		{
			return false;
		}

		// Replace any placeholders (e.g. {id}) in the rules with
		// the value found in $data, if any.
		self::$rules = self::FILL_PLACEHOLDERS(self::$rules, $data);


		// Need this for searching arrays in validation.
		hkm_helper('array');

		// Run through each rule. If we have any field set for
		// this rule, then we need to run them through!
		foreach (self::$rules as $field => $setup)
		{
			// Blast $rSetup apart, unless it's already an array.
			$rules = $setup['rules'] ?? $setup;

			if (is_string($rules))
			{
				$rules = self::SPLIT_RULES($rules);
			}

			$values = hkm_dot_array_search($field, $data);
			$values = is_array($values) ? $values : [$values];

			if ($values === [])
			{
				// We'll process the values right away if an empty array
				self::PROCESS_RULES($field, $setup['label'] ?? $field, $values, $rules, $data);
			}

			foreach ($values as $value)
			{
				// Otherwise, we'll let the loop do the job
				self::PROCESS_RULES($field, $setup['label'] ?? $field, $value, $rules, $data);
			}
		}

		return self::GET_ERRORS() === [];
	}

	/**
	 * Runs the validation process, returning true or false
	 * determining whether validation was successful or not.
	 *
	 * @param mixed    $value
	 * @param string   $rule
	 * @param string[] $errors
	 *
	 * @return boolean
	 */
	public static function CHECK($value, string $rule, array $errors = []): bool
	{
		self::RESET();

		return self::SET_RULE('check', null, $rule, $errors)::RUN(['check' => $value]);
	}

	protected static function run_in_instance($rule,$value,$param,$data,$error)
	{
		$found = false;
		$passed = false;
		      foreach (self::$ruleSetInstances as $set)
				{
					if (method_exists($set, $rule))
					{
						
						$found  = true;
						$passed = $param === false ? $set->$rule($value, $error) : $set->$rule($value, $param, $data, $error);
						
						break;
					}
					
				}
		return [$found,$passed];
		
	}

	/**
	 * Runs all of $rules against $field, until one fails, or
	 * all of them have been processed. If one fails, it adds
	 * the error to self::$errors and moves on to the next,
	 * so that we can collect all of the first errors.
	 *
	 * @param string       $field
	 * @param string|null  $label
	 * @param string|array $value
	 * @param array|null   $rules
	 * @param array        $data
	 *
	 * @return boolean
	 */
	protected static function PROCESS_RULES(string $field, string $label = null, $value, $rules = null, array $data = null): bool
	{

		
	

		if (is_null($data))
		{
			throw new InvalidArgumentException('You must supply the parameter: data.');
		}

		if (in_array('if_exist', $rules, true))
		{
			$flattenedData = hkm_array_flatten_with_dots($data);
			$ifExistField  = $field;

			if (strpos($field, '.*') !== false)
			{
				// We'll change the dot notation into a PCRE pattern
				// that can be used later
				$ifExistField = str_replace('\.\*', '\.(?:[^\.]+)', preg_quote($field, '/'));

				$dataIsExisting = array_reduce(array_keys($flattenedData), static function ($carry, $item) use ($ifExistField) {
					$pattern = sprintf('/%s/u', $ifExistField);
					return $carry || preg_match($pattern, $item) === 1;
				}, false);
			}
			else
			{
				$dataIsExisting = array_key_exists($ifExistField, $flattenedData);
			}

			unset($ifExistField, $flattenedData);

			if (! $dataIsExisting)
			{
				// we return early if `if_exist` is not satisfied. we have nothing to do here.
				return true;
			}

			// Otherwise remove the if_exist rule and continue the process
			$rules = array_diff($rules, ['if_exist']);
		}

		

		if (in_array('permit_empty', $rules, true))
		{
			if (! in_array('required', $rules, true) && (is_array($value) ? empty($value) : (trim($value) === '')))
			{
				$passed = true;

				foreach ($rules as $rule)
				{
					if (preg_match('/(.*?)\[(.*)\]/', $rule, $match))
					{
						$rule  = $match[1];
						$param = $match[2];

						if (! in_array($rule, ['required_with', 'required_without'], true))
						{
							continue;
						}

						// Check in our rulesets
						foreach (self::$ruleSetInstances as $set)
						{
							if (! method_exists($set, $rule))
							{
								continue;
							}

							$passed = $passed && $set->$rule($value, $param, $data);
							break;
						}
					}
				}

				if ($passed === true)
				{
					return true;
				}
			}

			$rules = array_diff($rules, ['permit_empty']);
		}


		foreach ($rules as $rule)
		{
			$isCallable = is_callable($rule);

			$passed = false;
			$param  = false;

			if (! $isCallable && preg_match('/(.*?)\[(.*)\]/', $rule, $match))
			{
				$rule  = $match[1];
				$param = $match[2];
			}



			// Placeholder for custom errors from the rules.
			$error = null;

			// If it's a callable, call and and get out of here.
			if ($isCallable)
			{
				$passed = $param === false ? $rule($value) : $rule($value, $param, $data);
			}
			else
			{
				$found = false;

				// Check in our rulesets
				 
				$retun = self::run_in_instance($rule,$value,$param,$data,$error);
                $found = $retun[0];
				$passed = $retun[1];

				// If the rule wasn't found anywhere, we
				// should throw an exception so the developer can find it.
				if (! $found)
				{
					throw ValidationException::FOR_RULE_NOT_FOUND($rule);
				}
			}

			// Set the error message if we didn't survive.
			if ($passed === false)
			{
				// if the $value is an array, convert it to as string representation
				if (is_array($value))
				{
					$value = '[' . implode(', ', $value) . ']';
				}

				self::$errors[$field] = is_null($error)
					? self::GET_ERROR_MESSAGE($rule??'', $field??'', $label??'', $param??'', $value??'')
					: $error; // @phpstan-ignore-line

					
				return false;
			}
		}
		return true;
	}



	/**
	 * Takes a Request object and grabs the input data to use from its
	 * array values.
	 *
	 * @param Request|IncomingRequest $request
	 *
	 * @return ValidationInterface
	 */
	public static function WITH_REQUEST(Request $request): ValidationInterface
	{
		/** @var IncomingRequest $request */
		if (strpos($request::GET_HEADER_LINE('Content-Type'), 'application/json') !== false)
		{
			self::$data = $request::GET_JSON(true);
			return self::$thiss;
		}

		if (in_array($request::GET_METHOD(), ['put', 'patch', 'delete'], true)
			&& strpos($request::GET_HEADER_LINE('Content-Type'), 'multipart/form-data') === false
		)
		{
			self::$data = $request::GET_RAW_INPUT();
		}
		else
		{
			self::$data = $request::GET_VAR() ?? [];
		}

		return self::$thiss;
	}

	/**
	 * Sets an individual rule and custom error messages for a single field.
	 *
	 * The custom error message should be just the messages that apply to
	 * this field, like so:
	 *
	 *    [
	 *        'rule' => 'message',
	 *        'rule' => 'message'
	 *    ]
	 *
	 * @param string      $field
	 * @param string|null $label
	 * @param string      $rules
	 * @param array       $errors
	 *
	 * @return $this
	 */
	public static function SET_RULE(string $field, string $label = null, string $rules, array $errors = [])
	{
		self::$rules[$field] = [
			'label' => $label,
			'rules' => $rules,
		];

		self::$customErrors = array_merge(self::$customErrors, [
			$field => $errors,
		]);

		return self::$thiss;
	}

	/**
	 * Stores the rules that should be used to validate the items.
	 * Rules should be an array formatted like:
	 *
	 *    [
	 *        'field' => 'rule1|rule2'
	 *    ]
	 *
	 * The $errors array should be formatted like:
	 *    [
	 *        'field' => [
	 *            'rule' => 'message',
	 *            'rule' => 'message
	 *        ],
	 *    ]
	 *
	 * @param array $rules
	 * @param array $errors // An array of custom error messages
	 *
	 * @return ValidationInterface
	 */
	public static function SET_RULES(array $rules, array $errors = []): ValidationInterface
	{
		self::$customErrors = $errors;

		foreach ($rules as $field => &$rule)
		{
			if (! is_array($rule))
			{
				continue;
			}

			if (! array_key_exists('errors', $rule))
			{
				continue;
			}

			self::$customErrors[$field] = $rule['errors'];
			unset($rule['errors']);
		}

		self::$rules = $rules;

		return self::$thiss;
	}

	/**
	 * Returns all of the rules currently defined.
	 *
	 * @return array
	 */
	public static function GET_RULES(): array
	{
		return self::$rules;
	}

	/**
	 * Checks to see if the rule for key $field has been set or not.
	 *
	 * @param string $field
	 *
	 * @return boolean
	 */
	public static function HAS_RULE(string $field): bool
	{
		return array_key_exists($field, self::$rules);
	}

	/**
	 * Get rule group.
	 *
	 * @param string $group Group.
	 *
	 * @return string[] Rule group.
	 *
	 * @throws InvalidArgumentException If group not found.
	 */
	public static function GET_RULE_GROUP(string $group): array
	{
		if (! isset(self::$config::$$group))
		{
			throw ValidationException::FOR_GROUP_NOT_FOUND($group);
		}

		if (! is_array(self::$config::$$group))
		{
			throw ValidationException::FOR_GROUP_NOT_ARRAY($group);
		}

		return self::$config::$$group;
	}

	/**
	 * Set rule group.
	 *
	 * @param string $group Group.
	 *
	 * @throws InvalidArgumentException If group not found.
	 */
	public static function SET_RULE_GROUP(string $group)
	{
		$rules = self::GET_RULE_GROUP($group);
		self::SET_RULES($rules);

		$errorName = $group . '_errors';
		if (isset(self::$config::$$errorName))
		{
			self::$customErrors = self::$config::$$errorName;
		}
	}

	/**
	 * Returns the rendered HTML of the errors as defined in $template.
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public static function LIST_ERRORS(string $template = 'list'): string
	{
		if (! array_key_exists($template, self::$config::$templates))
		{
			throw ValidationException::FOR_INVALID_TEMPLATE($template);
		}

		return self::$view
			 ::SET_VAR('errors', self::GET_ERRORS())
			 ::RENDER(self::$config::$templates[$template]);
	}

	/**
	 * Displays a single error in formatted HTML as defined in the $template view.
	 *
	 * @param string $field
	 * @param string $template
	 *
	 * @return string
	 */
	public static function SHOW_ERROR(string $field, string $template = 'single'): string
	{
		if (! array_key_exists($field, self::GET_ERRORS()))
		{
			return '';
		}

		if (! array_key_exists($template, self::$config::$templates))
		{
			throw ValidationException::FOR_INVALID_TEMPLATE($template);
		}

		return self::$view
		        ::SET_VAR('error', self::GET_ERROR($field))
			    ::RENDER(self::$config::$templates[$template]);
	}

	/**
	 * Loads all of the rulesets classes that have been defined in the
	 * Validation and stores them locally so we can use them.
	 */
	protected static function LOAD_RULE_SETS()
	{
		if (empty(self::$ruleSetFiles))
		{
			throw ValidationException::FOR_NO_RULE_SETS();
		}

		foreach (self::$ruleSetFiles as $file)
		{
			self::$ruleSetInstances[] = new $file();
		}
	}

	/**
	 * Loads custom rule groups (if set) into the current rules.
	 *
	 * Rules can be pre-defined in Validation and can
	 * be any name, but must all still be an array of the
	 * same format used with SET_RULES(). Additionally, check
	 * for {group}_errors for an array of custom error messages.
	 *
	 * @param string|null $group
	 *
	 * @return array|ValidationException|null
	 */
	public static function LOAD_RULE_GROUP(string $group = null)
	{
		if (empty($group))
		{
			return null;
		}

		if (! isset(self::$config::$$group))
		{
			throw ValidationException::FOR_GROUP_NOT_FOUND($group);
		}

		if (! is_array(self::$config::$$group))
		{
			throw ValidationException::FOR_GROUP_NOT_ARRAY($group);
		}

		self::SET_RULES(self::$config::$$group);

		// If {group}_errors exists in the config file,
		// then override our custom errors with them.
		$errorName = $group . '_errors';

		if (isset(self::$config::$$errorName))
		{
			self::$customErrors = self::$config::$$errorName;
		}

		return self::$rules;
	}

	/**
	 * Replace any placeholders within the rules with the values that
	 * match the 'key' of any properties being set. For example, if
	 * we had the following $data array:
	 *
	 * [ 'id' => 13 ]
	 *
	 * and the following rule:
	 *
	 *  'required|is_unique[users,email,id,{id}]'
	 *
	 * The value of {id} would be replaced with the actual id in the form data:
	 *
	 *  'required|is_unique[users,email,id,13]'
	 *
	 * @param array $rules
	 * @param array $data
	 *
	 * @return array
	 */
	protected static function FILL_PLACEHOLDERS(array $rules, array $data): array
	{
		$replacements = [];

		foreach ($data as $key => $value)
		{
			$replacements["{{$key}}"] = $value;
		}

		if (! empty($replacements))
		{
			foreach ($rules as &$rule)
			{
				if (is_array($rule))
				{
					foreach ($rule as &$row)
					{
						// Should only be an `errors` array
						// which doesn't take placeholders.
						if (is_array($row))
						{
							continue;
						}

						$row = strtr($row, $replacements);
					}
					continue;
				}

				$rule = strtr($rule, $replacements);
			}
		}


		return $rules;
	}

	/**
	 * Checks to see if an error exists for the given field.
	 *
	 * @param string $field
	 *
	 * @return boolean
	 */
	public static function HAS_ERROR(string $field): bool
	{
		return array_key_exists($field, self::GET_ERRORS());
	}

	/**
	 * Returns the error(s) for a specified $field (or empty string if not
	 * set).
	 *
	 * @param string $field Field.
	 *
	 * @return string Error(s).
	 */
	public static function GET_ERROR(string $field = null): string
	{
		if ($field === null && count(self::$rules) === 1)
		{
			$field = array_key_first(self::$rules);
		}

		return array_key_exists($field, self::GET_ERRORS()) ? self::$errors[$field] : '';
	}

	/**
	 * Returns the array of errors that were encountered during
	 * a run() call. The array should be in the following format:
	 *
	 *    [
	 *        'field1' => 'error message',
	 *        'field2' => 'error message',
	 *    ]
	 *
	 * @return array<string,string>
	 *
	 * Excluded from code coverage because that it always run as cli
	 *
	 * @codeCoverageIgnore
	 */
	public static function GET_ERRORS(): array
	{
		// If we already have errors, we'll use those.
		// If we don't, check the session to see if any were
		// passed along from a redirect_with_input request.
		if (empty(self::$errors) && ! hkm_is_cli() )
		{
			$SESSION = ServicesSystem::SESSION();
			if ( $SESSION->has('_hkm_validation_errors')) {
				self::$errors = $SESSION->get('_hkm_validation_errors');
			}
		}

		return self::$errors ?? [];
	}

	public static function CLEAR_ERRORS()
	{
		self::$errors = [];
	}

	/**
	 * Sets the error for a specific field. Used by custom validation methods.
	 *
	 * @param string $field
	 * @param string $error
	 *
	 * @return self
	 */
	public static function SET_ERROR(string $field, string $error): self
	{
		self::$errors[$field] = $error;

		return self::$thiss;
	}

	/**
	 * Attempts to find the appropriate error message
	 *
	 * @param string      $rule
	 * @param string      $field
	 * @param string|null $label
	 * @param string      $param
	 * @param string      $value The value that caused the validation to fail.
	 *
	 * @return string
	 */
	protected static function GET_ERROR_MESSAGE(string $rule, string $field, string $label = ' ', string $param = ' ', string $value = ' '): string
	{
		// Check if custom message has been defined by user
		if (isset(self::$customErrors[$field][$rule]))
		{
			$message = hkm_lang(self::$customErrors[$field][$rule]);
		}
		else
		{
			// Try to grab a localized version of the message...
			// hkm_lang() will return the rule name back if not found,
			// so there will always be a string being returned.
			$message = hkm_lang('Validation.' . $rule);
		}

		$message = str_replace('{field}', empty($label) ? $field : hkm_lang($label), $message);
		$message = str_replace('{param}', empty(self::$rules[$param]['label']) ? $param : hkm_lang(self::$rules[$param]['label']), $message);

		return str_replace('{value}', $value, $message);
	}

	/**
	 * Split rules string by pipe operator.
	 *
	 * @param string $rules
	 *
	 * @return array
	 */
	protected static function SPLIT_RULES(string $rules): array
	{
		$nonEscapeBracket = '((?<!\\\\)(?:\\\\\\\\)*[\[\]])';
		$pipeNotInBracket = sprintf(
			'/\|(?=(?:[^\[\]]*%s[^\[\]]*%s)*(?![^\[\]]*%s))/',
			$nonEscapeBracket,
			$nonEscapeBracket,
			$nonEscapeBracket
		);

		$_rules = preg_split($pipeNotInBracket, $rules);

		return array_unique($_rules);
	}

	// Misc
	/**
	 * Resets the class to a blank slate. Should be called whenever
	 * you need to process more than one array.
	 *
	 * @return ValidationInterface
	 */
	public static function RESET(): ValidationInterface
	{
		self::$data         = [];
		self::$rules        = [];
		self::$errors       = [];
		self::$customErrors = [];

		return self::$thiss;
	}
}
