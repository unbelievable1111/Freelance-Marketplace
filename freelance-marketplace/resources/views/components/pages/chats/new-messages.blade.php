@foreach ($messages as $message)
    <div data-id="{{ $message->id }}"
         class="d-flex flex-column mb-3 {{ $message->sender_id === auth()->id() ? 'align-items-end' : 'align-items-start' }}">

        <div class="card border-0 shadow-sm p-2
            {{ $message->sender_id === auth()->id() ? 'bg-primary text-white' : 'bg-white text-dark' }}"
            style="max-width:80%; min-width:300px; width:fit-content;">

            <div class="card-body p-2">
                <p class="mb-0">{{ $message->message }}</p>
            </div>
        </div>

        <small class="mt-1 opacity-75">
            {{ $message->sender->name }} • {{ $message->created_at->diffForHumans() }}
        </small>
    </div>
@endforeach