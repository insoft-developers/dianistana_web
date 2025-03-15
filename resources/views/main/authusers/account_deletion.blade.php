@extends("layouts.master_authusers")

@section("title_authuser","Account Deletion Request")

@section("content_authuser")

<div class="row">
    <div class="col-md-12 mb-3">
        
        <h2>Account Deletion</h2>
       
        <hr>
    </div>
    <form>
    <div class="col-md-12">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control">
        </div>
    </div>
   
    
    <div class="col-12">
        <div class="mb-4">
            <button type="submit" id="btnSignIn" class="btn btn-secondary w-100">Request Account Deletion</button>
        </div>
    </div>
</form>
    
   
   
</div>      

@endsection