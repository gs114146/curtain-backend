@forelse ($notifications->take(10) as $notification)
    @php
        $link = '';
        $data = json_decode($notification->data, true);
        if ($data != null && $data['link'] != null) {
            $link = $data['link'];
        }
    @endphp
    <li id="{{ 'notifiction-' . $notification->id }}" data-link="{{ $link }}"
        data-notification="{{ $notification->id }}"
        class="list-group-item list-group-item-action dropdown-notifications-item">
        <div class="d-flex">
            <div class="flex-grow-1 view-notification" data-link="{{ $link }}"
                data-notification="{{ $notification->id }}">
                <h6 class="small mb-0">{{ $notification->title }}</h6>
                <small class="mb-1 d-block text-body">{{ $notification->body }}</small>
                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
            </div>
            <div class="flex-shrink-0 dropdown-notifications-actions">
                <a href="javascript:void(0)" class="dropdown-notifications-read">
                    <span class="badge badge-dot"></span>
                </a>
                <a href="javascript:void(0)" class="dropdown-notifications-archive remove-notification"
                    data-parent="{{ $notification->id }}">
                    <span class="bx bx-x"></span>
                </a>
            </div>
        </div>
    </li>
@empty
@endforelse
