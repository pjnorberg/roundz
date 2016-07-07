<div class="col-md-5">
    <div class="form-group">
        <label for="name">Name</label>
        {{ Form::text('name', null, ['class' => 'form-control', 'id' => 'name', 'required' => 'required']) }}
    </div>
</div>
<div class="col-md-5">
    <div class="form-group">
        <label for="slug">Slug</label>
        {{ Form::text('slug', null, ['class' => 'form-control', 'id' => 'slug', 'required' => 'required']) }}
    </div>
</div>
<div class="col-md-2">
    <div class="form-group">
        <label>&nbsp;</label>
        <button class="btn btn-primary btn-block">Save</button>
    </div>
</div>