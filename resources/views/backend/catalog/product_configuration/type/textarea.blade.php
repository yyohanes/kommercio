<div id="configuration-type-{{ $type }}-wrapper" data-select_dependent="#type" data-select_dependent_value="{{ $type }}">
    @include('backend.master.form.fields.text', [
        'name' => $type.'[rules][min]',
        'label' => 'Minimum Character',
        'key' => $type.'.rules.min',
        'attr' => [
            'class' => 'form-control',
            'id' => $type.'[rules][min]',
        ],
        'defaultValue' => old($type.'.rules.min', $configuration->getData('rules.min', null)),
        'required' => FALSE
    ])

    @include('backend.master.form.fields.text', [
        'name' => $type.'[rules][max]',
        'label' => 'Maximum Character',
        'key' => $type.'.rules.max',
        'attr' => [
            'class' => 'form-control',
            'id' => $type.'[rules][max]',
        ],
        'defaultValue' => old($type.'.rules.max', $configuration->getData('rules.max', null)),
        'required' => FALSE
    ])
</div>