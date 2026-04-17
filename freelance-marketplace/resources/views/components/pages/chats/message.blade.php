<div data-id="{{ $message->id }}" class="d-flex flex-column mb-3
     {{ $message->sender_id === auth()->id() ? 'align-items-end' : 'align-items-start' }}">

    <div class="card border-0 shadow-sm p-2
        {{ $message->sender_id === auth()->id() ? 'bg-primary text-white' : 'bg-white text-dark' }}"
        style="max-width:80%; min-width:300px; width:fit-content;">

        <div class="card-body p-2">
            <p class="mb-0">{{ $message->message }}</p>
        </div>
    </div>

    <small class="message-time mt-1 opacity-75">

        <span class="sender-name">
            {{ $message->sender->name }}
        </span>

        •

        <span class="time"
              data-time="{{ $message->created_at->timestamp }}">
            {{ $message->created_at->diffForHumans() }}
        </span>

    </small>
</div>