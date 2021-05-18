@foreach ($list->getFields as $field)
	@if ($field->visible || !isset($is_page))
		@if ($field->tag != 'EMAIL')
			@if ($field->type == "text")
				@include('marketing::helpers.form_control', ['type' => $field->type, 'name' => $field->tag, 'label' => $field->label, 'value' => (isset($values[$field->tag]) ? $values[$field->tag] : $field->default_value), 'rules' => $list->getFieldRules()])
			@elseif ($field->type == "number")
				@include('marketing::helpers.form_control', ['type' => 'number', 'name' => $field->tag, 'label' => $field->label, 'value' => (isset($values[$field->tag]) ? $values[$field->tag] : $field->default_value), 'rules' => $list->getFieldRules()])
			@elseif ($field->type == "textarea")
				@include('marketing::helpers.form_control', ['type' => 'textarea', 'name' => $field->tag, 'label' => $field->label, 'value' => (isset($values[$field->tag]) ? $values[$field->tag] : $field->default_value), 'rules' => $list->getFieldRules()])
			@elseif ($field->type == "dropdown")
				@include('marketing::helpers.form_control', ['type' => 'select', 'name' => $field->tag, 'label' => $field->label, 'value' => (isset($values[$field->tag]) ? $values[$field->tag] : $field->default_value), 'options' => $field->getSelectOptions(), 'rules' => $list->getFieldRules()])
			@elseif ($field->type == "multiselect")
				@include('marketing::helpers.form_control', ['multiple' => true, 'type' => 'select', 'name' => $field->tag . "[]", 'label' => $field->label, 'value' => (isset($values[$field->tag]) ? $values[$field->tag] : $field->default_value), 'options' => $field->getSelectOptions(), 'rules' => $list->getFieldRules()])
			@elseif ($field->type == "checkbox")
				@include('marketing::helpers.form_control', ['multiple' => true, 'type' => 'checkboxes', 'name' => $field->tag . "[]", 'label' => $field->label, 'value' => (isset($values[$field->tag]) ? $values[$field->tag] : $field->default_value), 'options' => $field->getSelectOptions(), 'rules' => $list->getFieldRules()])
			@elseif ($field->type == "radio")
				@include('marketing::helpers.form_control', ['multiple' => true, 'type' => 'radio', 'name' => $field->tag, 'label' => $field->label, 'value' => (isset($values[$field->tag]) ? $values[$field->tag] : $field->default_value), 'options' => $field->getSelectOptions(), 'rules' => $list->getFieldRules()])
			@elseif ($field->type == "date")
				@include('marketing::helpers.form_control', ['multiple' => true, 'type' => 'date', 'name' => $field->tag . "[]", 'label' => $field->label, 'value' => (isset($values[$field->tag]) ? $values[$field->tag] : $field->default_value), 'options' => $field->getSelectOptions(), 'rules' => $list->getFieldRules()])
			@elseif ($field->type == "datetime")
				@include('marketing::helpers.form_control', ['multiple' => true, 'type' => 'datetime', 'name' => $field->tag . "[]", 'label' => $field->label, 'value' => (isset($values[$field->tag]) ? $values[$field->tag] : $field->default_value), 'options' => $field->getSelectOptions(), 'rules' => $list->getFieldRules()])
			@endif
		@else
			@include('marketing::helpers.form_control', ['type' => $field->type, 'name' => $field->tag, 'label' => $field->label, 'value' => (isset($values[$field->tag]) ? $values[$field->tag] : $field->default_value), 'rules' => $list->getFieldRules()])
		@endif
	@endif
@endforeach

