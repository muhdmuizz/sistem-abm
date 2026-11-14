@if(session('status'))<p class="notice success" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="notice error" role="alert">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>@endif
