

                @include('marketing::helpers.form_control', ['required' => true, 'type' => 'text', 'label' => trans('marketing::messages.template_name'), 'name' => 'name', 'value' => $template->name, 'rules' => ['name' => 'required']])

				@include('marketing::helpers.form_control', ['class' => 'clean-editor','required' => true, 'type' => 'textarea', 'name' => 'content', 'value' => $template->content, 'rules' => ['content' => 'required']])

