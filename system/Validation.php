<?php 
namespace System;
use System\QueryBuilder as DB;

class Validation 
{
    private $app;

    private $field_data = [];

    private $errors = [];

    public function __construct(App $app)
    {   
        $this->app = $app;
    }

    public function make($field, $rules, $errors = [])
    {
        if($this->app->request->method() !== 'POST') 
        {
            return $this;
        }

        if(is_array($field)) 
        {
            foreach ($field as $row) 
            {
                if(isset($row['field'], $row['rules'])) 
                {
                    continue;
                }

                $errors = $row['errors'] ?: [];

                $this->make($row['field'], $row['rules'], $errors);
            }
        }
        elseif(! isset($rules)) 
        {
            throw new Exception('make() called without $rules');
        }

        if(! is_string($field) && $field === '') 
        {
            throw new Exception('make() called without $field');
        }
        
        if(! is_array($rules)) 
        {
            if(! is_string($rules)) 
            {
                throw new Exception('make() expect $rules to be string or array');
            }

            $rules = preg_split('/\|(?![^\[]*\])/', $rules);
        }

        $this->field_data[$field] = [
            'field'    => $field,
            'rules'    => $rules,
            'errors'   => $errors,
        ];

        return $this;
    }

    public function run()
    {
        if(count($this->field_data) === 0) 
        {
            return false;
        }

        foreach ($this->field_data as $field => $row) 
        {
            $this->execute($row, $field, $row['rules']);
        }

        return empty($this->errors);
    }

    private function execute($row, $field, $rules)
    {
        foreach ($rules as $rule) {
            $param = $message = null;

            if(preg_match('/(.*?)\[(.*)\]/', $rule, $match))
            {
                $rule = $match[1];
                $param = $match[2];
            }

            $message = isset($this->field_data[$field]['errors'][$rule]) ? $this->field_data[$field]['errors'][$rule] : null;
            $param = $param ?: $message;

            if(method_exists($this, $rule)) $this->$rule($field, $param, $message); 
        }
    }

    public function required($input, $message = null)
    {
        if($this->hasError($input)) 
        {
            return $this;
        }

        $value = $this->app->request->post($input);

        $result = (is_array($value)) ? (empty($value) === false) : (trim($value) !== '');

        if(! $result)
        {
            $message = $message ?: sprintf("%s Is Required", ucfirst($input));
            $this->addError($input, $message);
        } 

        return $this;
    }

    public function required_file($input, $message = null)
    {
        if($this->hasError($input)) 
        {
            return $this;
        }

        $file = $this->app->request->file($input);

        if(! $file->exists())
        {
            $message = $message ?: sprintf("%s Is Required", ucfirst($input));
            $this->addError($input, $message);
        } 

        return $this;
    }

    public function image($input, $message = null)
    {
        if($this->hasError($input)) 
        {
            return $this;
        }

        $file = $this->app->request->file($input);

        if(! $file->isImage())
        {
            $message = $message ?: sprintf("%s Is Not Valid Image", ucfirst($input));
            $this->addError($input, $message);
        } 

        return $this;
    }

    public function email($input, $message = null)
    {
        if($this->hasError($input)) 
        {
            return $this;
        }

        $value = $this->app->request->post($input);

        if(! filter_var($value, FILTER_VALIDATE_EMAIL))
        {
            $message = $message ?: sprintf("%s Is Not Valid Email", ucfirst($input));
            $this->addError($input, $message);
        }
    }

    public function number($input, $message = null)
    {
        if($this->hasError($input)) 
        {
            return $this;
        }

        $value = $this->app->request->post($input);

        if(! is_numeric($value))
        {
            $message = $message ?: sprintf("%s Accept Only Number", ucfirst($input));
            $this->addError($input, $message);
        }
    }

    public function float($input, $message = null)
    {
        if($this->hasError($input)) 
        {
            return $this;
        }

        $value = $this->app->request->post($input);

        if(! is_float($value))
        {
            $message = $message ?: sprintf("%s Accept Only Float", ucfirst($input));
            $this->addError($input, $message);
        }
    }

    public function min($input, $length, $message = null)
    {
        if($this->hasError($input)) 
        {
            return $this;
        }

        $value = $this->app->request->post($input);

        if(strlen($value) <= (int)$length)
        {
            $message = $message ?: sprintf("%s Should Be At Least %d Charater", ucfirst($input), $length);
            $this->addError($input, $message);
        } 

        return $this;
    }

    public function max($input, $length, $message = null)
    {
        if($this->hasError($input)) 
        {
            return $this;
        }

        $value = $this->app->request->post($input);
        
        if(strlen($value) >= (int)$length)
        {
            $message = $message ?: sprintf("%s Should Be At Most %d Charater", ucfirst($input), $length);
            $this->addError($input, $message);
        } 

        return $this;
    }

    public function match($input, $ndInput, $message)
    {
        if($this->hasError($input)) 
        {
            return $this;
        }

        $value = $this->app->request->post($input);
        $ndValue = $this->app->request->post($ndInput);

        if($value !== $ndValue){
            $message = $message ?: sprintf("%s Should Match %s", ucfirst($input), ucfirst($ndInput));
            $this->addError($input, $message);
        } 

        return $this;
    }

    public function unique($input, $dbData, $message = null)
    {
        if($this->hasError($input)) 
        {
            return $this;
        }

        $value = $this->app->request->post($input);

        if(preg_match('/(.*?)\.(.*)/', $dbData, $match) === false)
        {
            return $this;
        }   

        array_shift($match);
        list($table, $column) = $match;

        if (DB::select($column)->from($table)->where("$column = ?" , $value)->fetch()) 
        {
            $message = $message ?: sprintf('%s Already Exists', ucfirst($input));
            $this->addError($input, $message);
        }

        return $this;
    }

    public function message($message)
    {
        $this->errors[] = $message;
    }

    public function getMessage()
    {
        return $this->errors;
    }

    public function flattenMessage()
    {
        return '<p>'. implode(' ', $this->errors) .'</p>';
    }

    private function prepare_rules($rules)
    {
        $new_rules = [];

        foreach ($rules as $rule) 
        {
            if($rule === 'required')
            {
                array_unshift($new_rules, 'required');
            }
            elseif($rule === 'required_file')
            {
                array_unshift($new_rules, 'required_file');
            }
            elseif($rule === 'isset' && (empty($new_rules) OR $new_rules[0] !== 'required'))
            {
                array_unshift($new_rules, 'isset');
            }
            else 
            {
                $new_rules[] = $rule;
            }
        }

        return $new_rules;
    }

    private function hasError($input)
    {
        return array_key_exists($input, $this->errors);
    }

    private function addError($input, $message)
    {
        $this->errors[$input] = $message;
    }
}