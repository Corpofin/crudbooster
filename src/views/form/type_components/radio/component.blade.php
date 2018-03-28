<div class='form-group {{$header_group_class}} {{ ($errors->first($name))?"has-error":"" }}' id='form-group-{{$name}}'
     style="{{@$formInput['style']}}">
    <label class='control-label col-sm-2'>{{$label}} {!!($required)?"<span class='text-danger' title='This field is required'>*</span>":"" !!}</label>
    <div class="{{$col_width?:'col-sm-10'}}">

        @if(!$formInput['dataenum'] && !$formInput['datatable'] && !$formInput['dataquery'])
            <em>{{cbTrans('there_is_no_option')}}</em>
        @endif

        @if($formInput['dataenum']!='')
            <?php
            @$value = explode(";", $value);
            @array_walk($value, 'trim');
            $dataenum = $formInput['dataenum'];
            $dataenum = (is_array($dataenum)) ? $dataenum : explode(";", $dataenum);
            ?>
            @foreach($dataenum as $k=>$d)
                <?php
                if (strpos($d, '|')) {
                    $val = substr($d, 0, strpos($d, '|'));
                    $label = substr($d, strpos($d, '|') + 1);
                } else {
                    $val = $label = $d;
                }
                $checked = ($value && in_array($val, $value)) ? "checked" : "";
                ?>
                <div class=" {{$disabled}}">
                    <label class='radio-inline'>
                        <input type="radio" {{$disabled}} {{$checked}} name="{{$name}}" value="{{$val}}"> {{$label}}
                    </label>
                </div>
            @endforeach
        @endif

        <?php

        if (@$formInput['datatable']):
            $datatable_array = explode(",", $formInput['datatable']);
            $datatable_tab = $datatable_array[0];
            $datatable_field = $datatable_array[1];

            $tables = explode('.', $datatable_tab);
            $selects_data = DB::table($tables[0])->select($tables[0].".id");

            if (\Schema::hasColumn($tables[0], 'deleted_at')) {
                $selects_data->where('deleted_at', NULL);
            }

            if (@$formInput['datatable_where']) {
                $selects_data->whereraw($formInput['datatable_where']);
            }

            if (count($tables)) {
                for ($i = 1; $i <= count($tables) - 1; $i++) {
                    $tab = $tables[$i];
                    $parent_table = $tables[$i - 1];
                    $fk_field = CRUDBooster::getForeignKey($parent_table, $tab);
                    $pk = CRUDBooster::findPrimaryKey($tab) ?: 'id';
                    $selects_data->leftjoin($tab, $tab.'.'.$pk, '=', $fk_field);
                }
            }

            //Because we use join statement, we need to select specified field to avoid ambigous
            $select_field = end($tables).'.'.$datatable_field;
            $select_field_alias = end($tables).'_'.$datatable_field;
            $selects_data->addselect($select_field.' as '.$select_field_alias);
            $selects_data = $selects_data->orderby(end($tables).'.'.$datatable_field, "asc")->get();

            foreach ($selects_data as $d) {
                $val = $d->{$select_field_alias};
                if ($val == '' || ! $d->id) continue;

                $checked = ($value == $d->id) ? "checked" : "";

                echo "
        <div data-val='$val' class='input-radio-wrapper $disabled'>
            <label class='radio-inline'>
                <input type='radio' $disabled $checked name='".$name."' value='".$d->id."'> ".$val."
            </label>
        </div>
        ";
            }

        endif;
        if ($formInput['dataquery']) {
            $query = DB::select(DB::raw($formInput['dataquery']));
            if ($query) {
                foreach ($query as $q) {
                    $checked = ($value == $q->value) ? "checked" : "";
                    echo "
        <div data-val='$val' class=' $disabled'>
            <label class='radio-inline'>
                <input type='radio' $disabled $checked name='".$name."' value='$q->value'> ".$q->label."
            </label>
        </div>
        ";
                }
            }
        }
        ?>
        {!! underField($formInput['help'], $errors->first($name)) !!}
    </div>
</div>