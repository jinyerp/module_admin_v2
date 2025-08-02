@if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('warning'))
    <div class="mb-4 p-3 bg-yellow-100 text-yellow-800 rounded">{{ session('warning') }}</div>
@endif

@if(session('info'))
    <div class="mb-4 p-3 bg-blue-100 text-blue-800 rounded">{{ session('info') }}</div>
@endif 