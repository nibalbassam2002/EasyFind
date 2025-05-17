@extends('layouts.dashboard')

@section('title', 'Show Feedback')

@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item"><a href="{{ route('moderator.feedback.index') }}">Manage Feedback</a></li>
    <li class="breadcrumb-item">Show Feedback</li>
@endsection

@section('contant')
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between align-items-center">
                        <span>Feedback Details</span>
                        <span class="badge rounded-pill bg-{{ $feedback->status == 'new' ? 'primary' : ($feedback->status == 'seen' ? 'secondary' : ($feedback->status == 'replied' ? 'success' : 'dark')) }}">
                            @if($feedback->status == 'new') New
                            @elseif($feedback->status == 'seen') Seen
                            @elseif($feedback->status == 'replied') Replied
                            @elseif($feedback->status == 'resolved') Resolved
                            @else {{ ucfirst($feedback->status) }}
                            @endif
                        </span>
                    </h5>

                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th scope="row" style="width: 150px;">From User:</th>
                                <td>{{ $feedback->user->name ?? 'Deleted User' }} ({{ $feedback->user->email ?? 'N/A' }})</td>
                            </tr>
                            <tr>
                                <th scope="row">Submission Date:</th>
                                <td>{{ $feedback->created_at->format('Y-m-d H:i A') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Type:</th>
                                <td>
                                    @if($feedback->type == 'complaint') Complaint
                                    @elseif($feedback->type == 'suggestion') Suggestion
                                    @elseif($feedback->type == 'improvement') Improvement Request
                                    @else Other
                                    @endif
                                </td>
                            </tr>
                            @if($feedback->subject)
                            <tr>
                                <th scope="row">Subject:</th>
                                <td>{{ $feedback->subject }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>

                    <h6 class="fw-bold mt-4">Message Content:</h6>
                    <p style="white-space: pre-wrap; background-color: #f8f9fa; padding: 15px; border-radius: 5px;">{{ $feedback->message }}</p>
                </div>
            </div>

            <!-- Admin Reply (if exists) -->
            @if($feedback->admin_reply)
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-success">Admin Response</h5>
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th scope="row" style="width: 150px;">Replied By:</th>
                                <td>{{ $feedback->replier->name ?? 'Administrator' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Reply Date:</th>
                                <td>{{ $feedback->replied_at ? $feedback->replied_at->format('Y-m-d H:i A') : 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p style="white-space: pre-wrap; background-color: #e6f7f0; padding: 15px; border-radius: 5px;">{{ $feedback->admin_reply }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-5">
            <!-- Reply to Feedback / Update Status -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Actions</h5>

                    @if($feedback->status != 'replied' && $feedback->status != 'resolved')
                    <form action="{{ route('moderator.feedback.reply', $feedback->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="admin_reply" class="form-label">Write your response:</label>
                            <textarea name="admin_reply" id="admin_reply" rows="5" class="form-control @error('admin_reply') is-invalid @enderror" required>{{ old('admin_reply') }}</textarea>
                            @error('admin_reply')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="bi bi-send"></i> Send Response</button>
                    </form>
                    <hr>
                    @endif

                    <form action="{{ route('moderator.feedback.updateStatus', $feedback->id) }}" method="POST" class="mt-3">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="status" class="form-label">Change Feedback Status:</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="new" {{ $feedback->status == 'new' ? 'selected' : '' }}>New</option>
                                <option value="seen" {{ $feedback->status == 'seen' ? 'selected' : '' }}>Seen</option>
                                <option value="replied" {{ $feedback->status == 'replied' ? 'selected' : '' }}>Replied</option>
                                <option value="resolved" {{ $feedback->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-circle"></i> Update Status</button>
                    </form>
                </div>
            </div>
            <a href="{{ route('moderator.feedback.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
        </div>
    </div>
@endsection