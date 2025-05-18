@extends('layouts.dashboard')

@section('title', 'Manage Feedback')

@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item">
        @auth
            @switch(auth()->user()->role)
                @case('admin')
                    Admin
                    @break
                @case('moderator')
                    Moderation
                    @break
                @default
                    Dashboard
            @endswitch
        @endauth
    </li>
    <li class="breadcrumb-item"><a href="{{ route('moderator.feedback.index') }}">Manage Feedback</a></li>
@endsection

@section('contant')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Feedback List</h5>

                <div class="table-responsive">
                    <table class="table "> {{-- You can use datatable class if you're using a table library --}}
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">User</th>
                                <th scope="col">Type</th>
                                <th scope="col">Title/Excerpt</th>
                                <th scope="col">Status</th>
                                <th scope="col">Submission Date</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($feedbacks as $feedback)
                            <tr>
                                <th scope="row">{{ $feedback->id }}</th>
                                <td>{{ $feedback->user->name ?? 'Deleted User' }} <br><small>({{ $feedback->user->email ?? 'N/A' }})</small></td>
                                <td>
                                    @if($feedback->type == 'complaint') <span class="badge bg-danger">Complaint</span>
                                    @elseif($feedback->type == 'suggestion') <span class="badge bg-info">Suggestion</span>
                                    @elseif($feedback->type == 'improvement') <span class="badge bg-warning text-dark">Improvement</span>
                                    @else <span class="badge bg-secondary">Other</span>
                                    @endif
                                </td>
                                <td>
                                    @if($feedback->subject)
                                        <strong>{{ Str::limit($feedback->subject, 40) }}</strong><br>
                                        <small class="text-muted">{{ Str::limit($feedback->message, 60) }}</small>
                                    @else
                                        {{ Str::limit($feedback->message, 70) }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-{{ $feedback->status == 'new' ? 'primary' : ($feedback->status == 'seen' ? 'secondary' : ($feedback->status == 'replied' ? 'success' : 'dark')) }}">
                                        @if($feedback->status == 'new') New
                                        @elseif($feedback->status == 'seen') Seen
                                        @elseif($feedback->status == 'replied') Replied
                                        @elseif($feedback->status == 'resolved') Resolved
                                        @else {{ ucfirst($feedback->status) }}
                                        @endif
                                    </span>
                                </td>
                                <td>{{ $feedback->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('moderator.feedback.show', $feedback->id) }}" class="btn btn-sm btn-outline-gold1">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No feedback available.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- End Table with stripped rows -->
                <div class="mt-3 d-flex justify-content-center">
                    {{ $feedbacks->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    {{-- Any scripts would go here --}}
    
@endsection