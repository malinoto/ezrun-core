<?php
namespace Ezrun\Core;

/*
FORM PROPERTIES:
array(
	'name',
	array(
		'method',
		'upload',				=> boolean				-> sets form enctype - default is false
		'action',
		'show_required'                         => boolean				-> show required fields label - default is false
		'no_id'					=> boolean				-> do not set form id - default is false
	)
)
=================================
FIELD PROPERTIES:
array(
	'name',
	array(
            'value' 				=> text						-> sets default field value
            'text',				=> boolean					-> accepts all characters and numbers - default is TRUE
            'numeric',				=> boolean					-> accepts only integers - deafult is FALSE
            'float',				=> boolean                                      -> accepts only numbers (transforms to float) - default is FALSE
            'email',				=> boolean                                      -> accepts only valid email - default is FALSE
            'array',				=> boolean                                      -> accepts only arrays - default is FALSE
            'multiple',				=> boolean					-> accepts multiple select options - default is FALSE
            'selected',				=> array					-> accepts array of selected options values if array or multiple
            'required',				=> boolean					-> set field as mandatory - default is FALSE
            'default_value'                     => text						-> set default value of the field - default is empty
            'accept_default',                   => boolean					-> if set to TRUE and 'required' option is FALSE
                                                                                                   it accepts the default value as valid - default is TRUE
            'options',				=> array(					-> array of predefined options for select
                                                    'option value',
                                                    'option label'
						)
            'show_label',			=> boolean					-> if set to TRUE field label will be displayed - default is FALSE
            'new_line',				=> boolean					-> if set to TRUE every field will be displayed one under another - default is FALSE
            'error'				=> string                                       -> if field is wrong sets error message
            'class'				=> text                                         -> sets the field style class
            'constant'				=> boolean					-> if the field should not be updated by post/get (value should be set) - default is false
            'first_empty'			=> boolean					-> set an empty option to a select in the beginning - default is false
            'label_class'			=> text						-> set css class on the labels of radio or checkbox
	)
)

You may set custom field attributes by adding them to the array!
Example: array('fieldname', array('value' => 'some value', 'onclick' => 'changeValue()'));
*/
class Form extends BaseCore {

    private $_form, $_required_label;
    private $service_attributes = array(
        'value',
        'text',
        'email',
        'numeric',
        'float',
        'array',
        'options',
        'multiple',
        'required',
        'accept_default',
        'show_label',
        'error',
        'default_value',
        'constant',
        'first_empty',
        'new_line',
        'label_class',
        'use_data'
    );

    public function __construct( array $properties ) {

        $this->_form = $this;

        //set defaults
        if(!isset($properties[1]['method'])) $properties[1]['method'] = 'POST';
        if(!isset($properties[1]['action'])) $properties[1]['action'] = '';
        if(!isset($properties[1]['upload'])) $properties[1]['upload'] = false;
        if(!isset($properties[1]['show_required'])) $properties[1]['show_required'] = false;
        if(!isset($properties[1]['target'])) $properties[1]['target'] = '';

        $properties[1]['method'] = strtoupper($properties[1]['method']);

        //translate
        $this->_required_label      = 'required';

        $this->_form->properties    = $properties;
        $this->_form->fields        = array();
    }

    public function setText( array $properties ) {
        $properties['type'] = 'text';
        $this->_form->fields[] = $properties;
    }

    public function setPassword( array $properties ) {
        $properties['type'] = 'password';
        $this->_form->fields[] = $properties;
    }

    public function setTextarea( array $properties ) {
        $properties['type'] = 'textarea';
        $this->_form->fields[] = $properties;
    }

    public function setSelect( array $properties ) {
        $properties['type'] = 'select';
        $this->_form->fields[] = $properties;
    }

    public function setCheckbox( array $properties ) {

        $properties['type'] = 'checkbox';

        //fix array property
        /*if(!isset($properties[1]['array']) || !$properties[1]['array']) {
                $properties[1]['array'] = true;
        }*/

        $this->_form->fields[] = $properties;
    }

    public function setRadio( array $properties ) {
        $properties['type'] = 'radio';
        $this->_form->fields[] = $properties;
    }

    public function setHidden( array $properties ) {
        $properties['type'] = 'hidden';
        $this->_form->fields[] = $properties;
    }

    public function setFile( array $properties ) {
        $properties['type'] = 'file';
        $this->_form->fields[] = $properties;

        //fix form enctype
        $this->_form->properties[1]['upload'] = true;
    }

    public function setSubmit( array $properties ) {
        $properties['type'] = 'submit';
        $this->_form->fields[] = $properties;
    }

    public function setButton( array $properties ) {
        $properties['type'] = 'button';
        $this->_form->fields[] = $properties;
    }

    public function getFormFields() {
        return $this->_form->fields;
    }

    public function getFormProperties() {
        return $this->_form->properties;
    }

    public function isPost() {

        $method = mb_strtoupper($this->_form->properties[1]['method']);
        $name = 'submit-' . $this->fixName($this->_form->properties[0]);

        eval("\$check_method = isset(\$_{$method}) && !empty(\$_{$method}) ? true : false;");
        eval("\$check_name = \$check_method && isset(\$_{$method}['{$name}']) ? true : false;");

        $submitted = $check_method && $check_name ? true : false;

        return $submitted;

    }

    public function isValid($boolean = false) {

        $valid = true;
        $fields = $this->getFormFields();
        $incorrect_fields = array();

        foreach($fields as $res) {

                if(isset($res[1]) && isset($res[1]['required']) && $res[1]['required']) {

                        $val = $this->checkFieldValue($res);

                        //check if empty
                        if(!is_numeric($val) && (!$val || empty($val))) {

                                $valid = false;
                                if(!$boolean) {
                                        array_push($incorrect_fields, $res[0]);
                                        setMessage((isset($res[1]['error']) ? $res[1]['error'] : ''));
                                }
                                else break;
                        }
                        //check if accepts default
                        else if(!isset($res[1]['accept_default']) || !$res[1]['accept_default']) {

                                $default = isset($res[1]['default_value']) ? $res[1]['default_value'] : false;

                                if( ( (isset($res[1]['multiple']) && $res[1]['multiple'])
                                        || (isset($res[1]['array']) && $res[1]['array']) )
                                        && in_array($default, $val)
                                ) {

                                        $valid = false;
                                        if(!$boolean) {
                                                array_push($incorrect_fields, $res[0]);
                                                setMessage((isset($res[1]['error']) ? $res[1]['error'] : ''));
                                        }
                                        else break;
                                }
                                else if($val === $default) {

                                        $valid = false;
                                        if(!$boolean) {
                                                array_push($incorrect_fields, $res[0]);
                                                setMessage((isset($res[1]['error']) ? $res[1]['error'] : ''));
                                        }
                                        else break;
                                }
                        }

                }
        }

        return $boolean ? $valid : $incorrect_fields;

    }

    public function getFormValues() {

        $array = array();
        $method = mb_strtoupper($this->_form->properties[1]['method']);

        $fields = $this->getFormFields();

        foreach($fields as $res) {

                $show_field = true;

                switch($res['type']) {

                        case 'text':
                        case 'password':
                        case 'textarea':
                        case 'select':
                        case 'hidden':
                        case 'radio':
                                eval("\$val = isset(\$_{$method}['{$res[0]}']) ? \$_{$method}['{$res[0]}'] : (isset(\$res[1]['value']) ? \$res[1]['value'] : false);");
                        break;

                        case 'checkbox':
                                eval("\$val = isset(\$_{$method}['{$res[0]}']) ? \$_{$method}['{$res[0]}'] : false;");
                                if(!$val) $show_field = false;
                        break;

                        case 'file':
                                $val = isset($_FILES[($res[0])]) && !empty($_FILES[($res[0])]['name']) ? $_FILES[($res[0])] : false;
                        break;

                        default:
                                $val = '';
                                if($method == 'GET') $show_field = false;
                        break;

                }

                if($show_field) {
                        $val = $this->checkSystemVariables($val, (isset($res[1]) ? $res[1] : array()));

                        $array[$res[0]] = $val;
                }
        }

        return $array;

    }

    public function generateForm() {

        $form = $this->renderFormhead();

        //set fields
        $fields = $this->getFormFields();
        foreach($fields as $res) {

                $field = '';

                switch($res['type']) {

                        //text field
                        case 'text':
                                $field = $this->renderText($res);
                        break;

                        //password
                        case 'password':
                                $field = $this->renderPassword($res);
                        break;

                        //textarea
                        case 'textarea':
                                $field = $this->renderTextarea($res);
                        break;

                        //select
                        case 'select':
                                $field = $this->renderSelect($res);
                        break;

                        //checkbox
                        case 'checkbox':
                                $field = $this->renderCheckbox($res);
                        break;

                        //radio
                        case 'radio':
                                $field = $this->renderRadio($res);
                        break;

                        //hidden field
                        case 'hidden':
                                $field = $this->renderHidden($res);
                        break;

                        //file field
                        case 'file':
                                $field = $this->renderFile($res);
                        break;

                        //submit button
                        case 'submit':
                                $field = $this->renderSubmit($res);
                        break;

                        //button
                        case 'button':
                                $field = $this->renderButton($res);
                        break;

                }

                $form .= $field;

                //check if required
                if(isset($res[1]['required']) && $res[1]['required'] && $this->_form->properties[1]['show_required']) {
                        $form .= ' <small class="red">* - ' . $this->_required_label . '</small>';
                }

        }

        $form .= $this->renderFormbottom();

        return $form;

    }

    public function renderField($fieldname, $part = array()) {

        $fields = $this->getFormFields();
        $element = '';

        foreach($fields as $res) {

                if($res[0] == $fieldname) {

                        switch($res['type']) {

                                case 'text':
                                        $element = $this->renderText($res, $part);
                                break;

                                case 'password':
                                        $element = $this->renderPassword($res);
                                break;

                                case 'textarea':
                                        $element = $this->renderTextarea($res);
                                break;

                                case 'select':
                                        $element = $this->renderSelect($res);
                                break;

                                case 'checkbox':
                                        $element = $this->renderCheckbox($res, $part);
                                break;

                                case 'radio':
                                        $element = $this->renderRadio($res, $part);
                                break;

                                case 'hidden':
                                        $element = $this->renderHidden($res);
                                break;

                                case 'file':
                                        $element = $this->renderFile($res);
                                break;

                                case 'submit':
                                        $element = $this->renderSubmit($res);
                                break;

                                case 'button':
                                        $element = $this->renderButton($res);
                                break;

                        }

                        //check if required
                        if(isset($res[1]['required']) && $res[1]['required'] && $this->_form->properties[1]['show_required']) {
                                $element .= ' <small class="red">* - ' . $this->_required_label . '</small>';
                        }

                        break;
                }

        }

        return $element;

    }

    public function getFieldValue($fieldname) {
        
        $fields = $this->getFormFields();

        foreach($fields as $res) {

                if($res[0] == $fieldname) {
                        return $this->checkFieldValue($res);
                        break;
                }

        }

        return false;
    }

    public function renderFormhead() {

        $form = sprintf('<form name="%1$s" action="%2$s">',
                                                $this->_form->properties[0],
                                                $this->_form->properties[1]['action']
                                        );

        //form id
        if(!isset($this->_form->properties[1]['no_id']) || !$this->_form->properties[1]['no_id']) {
                $form = preg_replace('/\>/iu', ' id="' . $this->_form->properties[0] . '">', $form);
        }

        //set file upload
        if($this->_form->properties[1]['upload']) {
                $form = preg_replace('/\>/iu', ' enctype="multipart/form-data">', $form);
        }

        //target
        if(!empty($this->_form->properties[1]['target'])) {
                $form = preg_replace('/\>/iu', ' target="' . $this->_form->properties[1]['target'] . '">', $form);
        }

        //set other attributes
        if(isset($this->_form->properties[1])) {
                foreach($this->_form->properties[1] as $key => $value) {
                        if(!empty($this->_form->properties[1][$key]) && !in_array($key, $this->service_attributes)) {
                                $form = preg_replace('/\>/iu', " " . strtolower($key) . "=\"{$this->_form->properties[1][$key]}\">", $form);
                        }
                }
        }

        return $form;

    }

    public function renderFormbottom() {

        $form = "\n" . '</form>';

        return $form;

    }

    private function renderText(array $properties, $part = array()) {

        $val = $this->checkFieldValue($properties);

        //fix name
        if(isset($properties[1]) && isset($properties[1]['array']) && $properties[1]['array']) {
                if(!preg_match('/(.*)\[\]$/iu', $properties[0])) $properties[0] = trim($properties[0]) . '[]';
        }

        if(empty($part)) {

                $field = "\n" . sprintf('<input type="text" name="%1$s" value="%2$s"/>',
                                                                                        $properties[0],
                                                                                        (isset($properties[1]['array']) && $properties[1]['array'] ? $val[0] : $val)
                                                                                );

                //set other attributes
                if(isset($properties[1])) {
                        foreach($properties[1] as $key => $value) {
                                if(!empty($properties[1][$key]) && !in_array($key, $this->service_attributes)) {
                                        $field = preg_replace('/\/\>/iu', " " . strtolower($key) . "=\"{$properties[1][$key]}\"/>", $field);
                                }
                        }
                }
        }
        else if(isset($properties[1]['array']) && $properties[1]['array']) {

                foreach($properties[1]['options'] as $key => $value) {

                        if(in_array($key, $part)) {

                                $field = "\n" . sprintf('<input type="text" name="%1$s" value="%2$s"/>',
                                                                                                        $properties[0],
                                                                                                        $value
                                                                                                );

                                //set other attributes
                                if(isset($properties[1])) {
                                        foreach($properties[1] as $key => $value) {
                                                if(!empty($properties[1][$key]) && !in_array($key, $this->service_attributes)) {
                                                        $field = preg_replace('/\/\>/iu', " " . strtolower($key) . "=\"{$properties[1][$key]}\"/>", $field);
                                                }
                                        }
                                }
                        }
                }
        }

        return $field;
    }

    private function renderPassword(array $properties) {

        $val = $this->checkFieldValue($properties);

        $field = "\n" . sprintf('<input type="password" name="%1$s" value="%2$s"/>',
                                                                                $properties[0],
                                                                                $val
                                                                        );

        //set other attributes
        if(isset($properties[1])) {
                foreach($properties[1] as $key => $value) {
                        if(!empty($properties[1][$key]) && !in_array($key, $this->service_attributes)) {
                                $field = preg_replace('/\/\>/iu', " " . strtolower($key) . "=\"{$properties[1][$key]}\"/>", $field);
                        }
                }
        }

        return $field;
    }

    private function renderTextarea(array $properties) {

        $val = $this->checkFieldValue($properties);

        $field = "\n" . sprintf('<textarea cols="0" rows="0" name="%1$s">%2$s</textarea>',
                                                                                $properties[0],
                                                                                $val
                                                                        );

        //set other attributes
        if(isset($properties[1])) {
                foreach($properties[1] as $key => $value) {
                        if(!empty($properties[1][$key]) && !in_array($key, $this->service_attributes)) {
                                $field = preg_replace('/"\>/iu', "\" " . strtolower($key) . "=\"{$properties[1][$key]}\">", $field);
                        }
                }
        }

        return $field;
    }

    private function renderSelect(array $properties) {

        $val = $this->checkFieldValue($properties);

        //fix name
        if(isset($properties[1]) && isset($properties[1]['multiple']) && $properties[1]['multiple']) {
                if(!preg_match('/(.*)\[\]$/iu', $properties[0])) $properties[0] = trim($properties[0]) . '[]';
        }

        $field = "\n" . sprintf('<select name="%1$s">', $properties[0]);

        //set other attributes
        $multiple = 0;
        if(isset($properties[1])) {

                foreach($properties[1] as $key => $value) {

                        if(!empty($properties[1][$key]) && !in_array($key, $this->service_attributes)) {
                                $field = preg_replace('/\>/iu', " " . strtolower($key) . "=\"{$properties[1][$key]}\">", $field);
                        }

                        //set multiple
                        if(($key == 'multiple' || $key == 'array') && $properties[1][$key]) {
                                $field = preg_replace('/\>/iu', " multiple>", $field);
                                $multiple = 1;
                        }
                }
        }

        //set options
        //set empty option
        if(isset($properties[1]['first_empty']) && $properties[1]['first_empty']) {

                $field .= "\n\t" . '<option value="">---</option>';
        }

        $index_val = $val == '' ? -1 : $val;
        foreach($properties[1]['options'] as $key => $value) {

                if($multiple == 1) {
                        $selected_option = (in_array($key, $index_val) ? 'selected="selected"' : '');
                }
                else $selected_option = ($index_val == $key ? 'selected="selected"' : '');

                if($key == 'empty') {

                        $value = "-----";
                        $selected_option = 'disabled="disabled"';
                }

                $data_attribute = "";
                $custom_value 	= $value;
                $custom_key 		= $key;

                if(is_array($value)) {

                        if(isset($properties[1]['use_data']) && $properties[1]['use_data'][0]) {

                                $data_value 		= array_pop($value);
                                $data_attribute = 'data-' . $properties[1]['use_data'][1] . '="' . $key . '"';

                                $custom_value 	= $value[0];
                                $custom_key 		= $data_value;
                        }
                        else {

                                $data_value 	= array_pop($value);
                                $custom_value = $data_value;
                                $custom_key 	= $key;
                        }
                }

                $field .= "\n\t" . "<option value=\"{$custom_key}\" {$selected_option} {$data_attribute}>{$custom_value}</option>";
        }

        $field .= "\n" . '</select>';

        return $field;
    }

    private function renderCheckbox(array $properties, $part = array()) {

        $val = $this->checkFieldValue($properties);

        $field = '';

        //fix array
        if(isset($properties[1]) && isset($properties[1]['array']) && $properties[1]['array']) {

                //fix name
                if(!preg_match('/(.*)\[\]$/iu', $properties[0])) $properties[0] = trim($properties[0]) . '[]';

                foreach($properties[1]['options'] as $key => $value) {

                        if(empty($part)) {

                                $sprintf = '<input type="checkbox" name="%1$s" value="%2$s" id="%1$s_%2$s"/>';
                                if(isset($properties[1]['show_label']) && $properties[1]['show_label']) 
                                        $sprintf .= '<label for="%1$s_%2$s" class="%4$s">%3$s</label>';
                                if(isset($properties[1]['new_line']) && $properties[1]['new_line'])
                                        $sprintf .= '<br/>';

                                $field_prep = "\n" . sprintf($sprintf,
                                                                                                $properties[0],
                                                                                                (!is_array($value) ? $value : $key),
                                                                                                (is_array($value) ? $value[count($value) - 1] : $value),
                                                                                                (isset($properties[1]['label_class']) ? trim($properties[1]['label_class']) : '')
                                                                                        );

                                //set checked
                                $check_value = is_numeric($value) ? $value : $key;
                                $checked_option = (!empty($val) && in_array($check_value, $val) ? 'checked="checked"' : '');
                                $field_prep = preg_replace('/\/\>/iu', " {$checked_option}/>", $field_prep);

                                $field .= $field_prep;
                        }
                        else if(in_array($key, $part)) {

                                $sprintf = '<input type="checkbox" name="%1$s" value="%2$s" id="%1$s_%2$s"/>';
                                if(isset($properties[1]['show_label']) && $properties[1]['show_label']) 
                                        $sprintf .= '<label for="%1$s_%2$s" class="%4$s">%3$s</label>';

                                $field_prep = "\n" . sprintf($sprintf,
                                                                                                $properties[0],
                                                                                                (is_numeric($value) ? $value : $key),
                                                                                                (is_numeric($value) ? '' : $value),
                                                                                                (isset($properties[1]['label_class']) ? trim($properties[1]['label_class']) : '')
                                                                                        );

                                //set checked
                                $check_value = is_numeric($value) ? $value : $key;
                                $checked_option = (!empty($val) && in_array($check_value, $val) ? 'checked="checked"' : '');
                                $field_prep = preg_replace('/\/\>/iu', " {$checked_option}/>", $field_prep);

                                $field .= $field_prep;
                        }

                }
        }
        else {
                $field .= "\n" . sprintf('<input type="checkbox" name="%1$s" value="%2$s" id="%1$s"/>',
                                                                                        $properties[0],
                                                                                        $properties[1]['options']
                                                                                );
                //set checked
                $checked_option = ($val == $properties[1]['options'] ? 'checked="checked"' : '');
                $field = preg_replace('/\/\>/iu', " {$checked_option}/>", $field);
        }

        //set other attributes
        if(isset($properties[1])) {
                foreach($properties[1] as $key => $value) {
                        if(!empty($properties[1][$key]) && !in_array($key, $this->service_attributes)) {
                                $field = preg_replace('/\/\>/iu', " " . strtolower($key) . "=\"{$properties[1][$key]}\"/>", $field);
                        }
                }
        }

        return $field;

    }

    private function renderRadio(array $properties, $part = array()) {

        $val = $this->checkFieldValue($properties);

        $field = '';

        foreach($properties[1]['options'] as $key => $value) {

                if(empty($part)) {

                        $sprintf = '<input type="radio" name="%1$s" value="%2$s" id="%1$s_%2$s"/>';
                        if(isset($properties[1]['show_label']) && $properties[1]['show_label']) 
                                $sprintf .= '<label for="%1$s_%2$s" class="%4$s">%3$s</label>';
                        if(isset($properties[1]['new_line']) && $properties[1]['new_line'])
                                        $sprintf .= '<br/>';

                        $field_prep = "\n" . sprintf($sprintf,
                                                                                        $properties[0],
                                                                                        (is_numeric($value) ? $value : $key),
                                                                                        (is_numeric($value) ? '' : $value),
                                                                                        (isset($properties[1]['label_class']) ? trim($properties[1]['label_class']) : '')
                                                                                );

                        //set checked
                        $check_value = is_numeric($value) ? $value : $key;
                        $checked_option = ($check_value == $val ? 'checked="checked"' : '');
                        $field_prep = preg_replace('/\/\>/iu', " {$checked_option}/>", $field_prep);

                        $field .= $field_prep;
                }
                else if(in_array($key, $part)) {

                        $sprintf = '<input type="radio" name="%1$s" value="%2$s" id="%1$s_%2$s"/>';
                        if(isset($properties[1]['show_label']) && $properties[1]['show_label']) 
                                $sprintf .= '<label for="%1$s_%2$s" class="%4$s">%3$s</label>';

                        $field_prep = "\n" . sprintf($sprintf,
                                                                                        $properties[0],
                                                                                        (is_numeric($value) ? $value : $key),
                                                                                        (is_numeric($value) ? '' : $value),
                                                                                        (isset($properties[1]['label_class']) ? trim($properties[1]['label_class']) : '')
                                                                                );

                        //set checked
                        $check_value = is_numeric($value) ? $value : $key;
                        $checked_option = ($check_value == $val ? 'checked="checked"' : '');
                        $field_prep = preg_replace('/\/\>/iu', " {$checked_option}/>", $field_prep);

                        $field .= $field_prep;
                }

        }

        //set other attributes
        if(isset($properties[1])) {
                foreach($properties[1] as $key => $value) {
                        if(!empty($properties[1][$key]) && !in_array($key, $this->service_attributes)) {
                                $field = preg_replace('/\/\>/iu', " " . strtolower($key) . "=\"{$properties[1][$key]}\"/>", $field);
                        }
                }
        }

        return $field;

    }

    private function renderHidden(array $properties, $part = array()) {

        $val = $this->checkFieldValue($properties);

        $field = '';

        //fix array
        if(isset($properties[1]) && isset($properties[1]['array']) && $properties[1]['array']) {

                //fix name
                if(!preg_match('/(.*)\[\]$/iu', $properties[0])) $properties[0] = trim($properties[0]) . '[]';

                foreach($properties[1]['options'] as $key => $value) {

                        if(empty($part)) {

                                $sprintf = '<input type="hidden" name="%1$s" value="%2$s" id="%1$s_%2$s"/>';

                                $field_prep = "\n" . sprintf($sprintf,
                                                                                                $properties[0],
                                                                                                (is_numeric($value) ? $value : $key),
                                                                                                (is_numeric($value) ? '' : $value)
                                                                                        );

                                $field .= $field_prep;
                        }
                        else if(in_array($key, $part)) {

                                $sprintf = '<input type="hidden" name="%1$s" value="%2$s" id="%1$s_%2$s"/>';

                                $field_prep = "\n" . sprintf($sprintf,
                                                                                                $properties[0],
                                                                                                (is_numeric($value) ? $value : $key),
                                                                                                (is_numeric($value) ? '' : $value)
                                                                                        );

                                $field .= $field_prep;
                        }

                }
        }
        else {

                //fix name
                if(isset($properties[1]) && isset($properties[1]['array']) && $properties[1]['array']) {
                        if(!preg_match('/(.*)\[\]$/iu', $properties[0])) $properties[0] = trim($properties[0]) . '[]';
                }

                $field = "\n" . sprintf('<input type="hidden" name="%1$s" value="%2$s"/>',
                                                                                        $properties[0],
                                                                                        (isset($properties[1]['array']) && $properties[1]['array'] ? $val[0] : $val)
                                                                                );
        }

        //set other attributes
        if(isset($properties[1])) {
                foreach($properties[1] as $key => $value) {
                        if(!empty($properties[1][$key]) && !in_array($key, $this->service_attributes)) {
                                $field = preg_replace('/\/\>/iu', " " . strtolower($key) . "=\"{$properties[1][$key]}\"/>", $field);
                        }
                }
        }

        return $field;

    }

    private function renderFile(array $properties) {

        //fix name
        if(isset($properties[1]) && isset($properties[1]['array']) && $properties[1]['array']) {
            if(!preg_match('/(.*)\[\]$/iu', $properties[0])) $properties[0] = trim($properties[0]) . '[]';
        }

        $field = "\n" . sprintf('<input type="file" name="%1$s" value=""/>',
                                                                                $properties[0]
                                                                        );
        //set other attributes
        if(isset($properties[1])) {
                foreach($properties[1] as $key => $value) {
                        if(!empty($properties[1][$key]) && !in_array($key, $this->service_attributes)) {
                                $field = preg_replace('/\/\>/iu', " " . strtolower($key) . "=\"{$properties[1][$key]}\"/>", $field);
                        }
                }
        }

        return $field;

    }

    private function renderSubmit(array $properties) {

        $val = $this->checkFieldValue($properties);

        $field = "\n" . sprintf('<input type="submit" id="%1$s" name="submit-%3$s" value="%2$s"/>',
                                                                                $properties[0],
                                                                                $val,
                                                                                $this->fixName($this->_form->properties[0])
                                                                        );

        //set other attributes
        if(isset($properties[1])) {
                foreach($properties[1] as $key => $value) {
                        if(!empty($properties[1][$key]) && !in_array($key, $this->service_attributes)) {
                                $field = preg_replace('/\/\>/iu', " " . strtolower($key) . "=\"{$properties[1][$key]}\"/>", $field);
                        }
                }
        }

        return $field;

    }

    private function renderButton(array $properties) {

        $val = $this->checkFieldValue($properties);

        $field = "\n" . sprintf('<input type="button" name="%1$s" value="%2$s"/>',
                                                                                $properties[0],
                                                                                $val
                                                                        );

        //set other attributes
        if(isset($properties[1])) {
                foreach($properties[1] as $key => $value) {
                        if(!empty($properties[1][$key]) && !in_array($key, $this->service_attributes)) {
                                $field = preg_replace('/\/\>/iu', " " . strtolower($key) . "=\"{$properties[1][$key]}\"/>", $field);
                        }
                }
        }

        return $field;

    }

    private function checkFieldValue(array $properties) {

        $value = isset($properties[1]['value']) ? $properties[1]['value'] : '';

        //constant
        if(isset($properties[1]) && isset($properties[1]['constant']) && $properties[1]['constant']) {

                return $value;
        }
        else {

                $method = mb_strtoupper($this->_form->properties[1]['method']);

                if($properties['type'] == 'file') {

                        $value = isset($_FILES[$properties[0]]) && !empty($_FILES[$properties[0]]['name']) ? $_FILES[$properties[0]] : false;
                } else if(!in_array($properties['type'], array('button', 'submit'))) {

                        $val = isset($properties[1]['value']) ? $properties[1]['value'] : (isset($properties[1]['default_value']) ? $properties[1]['default_value'] : false);

                        if($this->isPost()) {

                                eval("\$method_fix = \$_{$method};");
                                $val = isset($method_fix[$properties[0]]) ? $method_fix[$properties[0]] : false;
                        }

                        $value = $this->checkSystemVariables($val, (isset($properties[1]) ? $properties[1] : array()));
                }
        }

        return $value;
    }

    private function checkSystemVariables($value, $options) {

        $value = is_array($value) ? $value : trim($value);

        //check numeric
        if(isset($options['numeric']) && $options['numeric']) {
                $value = (int)$value;
        }

        //check float
        if(isset($options['float']) && $options['float']) {
                $value = (float)$value;
        }

        //check array
        if(isset($options['array']) && $options['array']) {
                $value = is_array($value) ? $value : (!empty($value) ? array($value) : array());
        }

        //check multiple
        if(isset($options['multiple']) && $options['multiple']) {
                $value = is_array($value) ? $value : (!empty($value) ? array($value) : array());
        }

        //check email
        if(isset($options['email']) && $options['email']) {
                $value = valid_email($value) ? $value : (isset($options['value']) ? $options['value'] : false);
        }

        return $value;

    }

    private function fixName($name) {

        $strip_array = array(' ','#','?','!','.',',','@','%','/','\\','"','&','|');

        $str = trim(strip_tags(htmlspecialchars_decode(urldecode($name))));
        $fixed_name = mb_strtolower(str_replace($strip_array, '-', $str), 'utf-8');

        return $fixed_name;

    }
}
