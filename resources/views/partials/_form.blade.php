<div class="col-md-4">
    <div class="form-group">
        <label for="name">Name</label>
        {{ Form::text('name', null, ['class' => 'form-control', 'id' => 'name', 'required' => 'required']) }}
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="slug">Slug</label>
        {{ Form::text('slug', null, ['class' => 'form-control', 'id' => 'slug', 'required' => 'required']) }}
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        <label for="instagramTag">Instagram-hashtag</label>
        {{ Form::text('instagram_tag', null, ['class' => 'form-control', 'id' => 'instagramTag']) }}
    </div>
</div>
<div class="col-md-1">
    <div class="form-group">
        <label>&nbsp;</label>
        <button class="btn btn-primary btn-block">Save</button>
    </div>
</div>