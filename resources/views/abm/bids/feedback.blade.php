@if(session('status'))<p class="notice success" role="status">{{ session('status') }}</p>@endif
@if($activationErrors->any())<div class="notice error" role="alert">@foreach($activationErrors->all() as $message)<p>{{ $message }}</p>@endforeach</div>@endif
