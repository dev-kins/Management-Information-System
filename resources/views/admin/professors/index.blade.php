@extends('layouts.admin')

@section('title', 'Manage Professors')

@section('content')
<div class="container mt-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-success mb-1">Manage Professors</h2>
            <p class="text-muted mb-0">Current school year: {{ $currentSchoolYear }}</p>
        </div>
        <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#addProfessorModal">
            <i class="bi bi-person-plus"></i> Add New Professor
        </button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- STATS CARDS --}}
    <div class="row mb-4">
        @foreach(['total'=>'Total Professors','withAdviser'=>'With Adviser','withoutAdviser'=>'Without Adviser','assignments'=>'Assigned Subjects'] as $key => $label)
            @php
                $colors = ['total'=>'success','withAdviser'=>'info','withoutAdviser'=>'warning','assignments'=>'primary'];
            @endphp
            <div class="col-md-3">
                <div class="card shadow-sm border-{{ $colors[$key] }}">
                    <div class="card-body text-center">
                        <h6 class="fw-bold">{{ $label }}</h6>
                        <h3 class="text-{{ $colors[$key] }}">{{ $stats[$key] }}</h3>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- FILTERS --}}
    <form method="GET" action="{{ route('admin.professors.index') }}" class="row g-3 mb-3">
       <div class="col-md-3">
    <input
        type="search"
        name="search"
        class="form-control"
        placeholder="Search by name or email"
        value="{{ request('search') }}"
    >
</div>

        <div class="col-md-3">
            <select name="grade_level_id" class="form-select">
                <option value="">All Adviser Grades</option>
                @foreach($gradeLevels as $level)
                    <option value="{{ $level->id }}" {{ request('grade_level_id') == $level->id ? 'selected' : '' }}>
                        Grade {{ $level->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success w-100">Filter</button>
        </div>
    </form>

    {{-- MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- VIEW TOGGLE --}}
    <div class="d-flex justify-content-end mb-3">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-success active" id="cardViewBtn"><i class="bi bi-grid-3x3-gap-fill"></i> Card View</button>
            <button type="button" class="btn btn-outline-success" id="listViewBtn"><i class="bi bi-list"></i> List View</button>
        </div>
    </div>

    @if($professors->count())
        {{-- CARD VIEW --}}
        <div id="cardView" class="row g-3">
            @foreach($professors as $professor)
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm rounded-3 h-100 professor-card">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $professor->name }}</h5>
                            <p class="card-text mb-1"><strong>Email:</strong> {{ $professor->email }}</p>
                            <p class="card-text mb-2">
                                <span class="badge {{ $professor->advisory && $professor->advisory->gradeLevel ? 'bg-secondary' : 'bg-warning' }}">
                                    {{ $professor->advisory && $professor->advisory->gradeLevel ? 'Adviser: Grade '.$professor->advisory->gradeLevel->name : ($professor->advisory ? 'Adviser assigned, grade missing' : 'Not assigned as adviser') }}
                                </span>
                            </p>
                            <div class="mt-auto d-flex flex-wrap gap-2">
                                <button class="btn btn-info btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#professorModal{{ $professor->id }}">
                                    <i class="bi bi-eye"></i> Manage
                                </button>
                                <button class="btn btn-success btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#assignModal{{ $professor->id }}">
                                    <i class="bi bi-journal-plus"></i> Assign Subject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- TABLE VIEW --}}
        <div id="listView" class="table-responsive d-none">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Adviser</th>
                        <th>Assignments</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($professors as $professor)
                        <tr>
                            <td>{{ $professor->name }}</td>
                            <td>{{ $professor->email }}</td>
                            <td>{{ $professor->advisory && $professor->advisory->gradeLevel ? 'Grade '.$professor->advisory->gradeLevel->name : '-' }}</td>
                            <td>{{ count($assignments[$professor->id] ?? []) }}</td>
                            <td>
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#professorModal{{ $professor->id }}"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#assignModal{{ $professor->id }}"><i class="bi bi-journal-plus"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $professors->appends(request()->query())->links() }}
        </div>
    @else
        <div class="alert alert-secondary text-center mt-4">
            No professors found.
        </div>
    @endif

    {{-- ADD PROFESSOR MODAL --}}
    <div class="modal fade" id="addProfessorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.professors.store') }}">
                @csrf
                <div class="modal-content shadow-sm rounded-3">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Add New Professor</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label fw-semibold">Full Name</label><input type="text" class="form-control" name="name" required></div>
                        <div class="mb-3"><label class="form-label fw-semibold">Email</label><input type="email" class="form-control" name="email" required></div>
                        <div class="alert alert-info small mb-0"><i class="bi bi-info-circle"></i> A temporary password will be generated and emailed to the professor.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"><i class="bi bi-plus-circle"></i> Add Professor</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- PROFESSOR MODALS --}}
    @foreach($professors as $professor)
        <!-- Professor Modal -->
        <div class="modal fade" id="professorModal{{ $professor->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-sm rounded-3">
                    <div class="modal-header bg-info text-white d-flex justify-content-between">
                        <h5 class="modal-title">Manage Professor: {{ $professor->name }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs mb-3" id="professorTab{{ $professor->id }}" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#details{{ $professor->id }}" type="button">Details</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#subjects{{ $professor->id }}" type="button">Subjects</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#adviser{{ $professor->id }}" type="button">Adviser</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#edit{{ $professor->id }}" type="button">Edit</button></li>
                            <li class="nav-item"><button class="nav-link text-danger" data-bs-toggle="tab" data-bs-target="#remove{{ $professor->id }}" type="button">Remove</button></li>
                        </ul>

                        <div class="tab-content">
                            <!-- Details -->
                            <div class="tab-pane fade show active" id="details{{ $professor->id }}">
                                <div class="row mb-3">
                                    <div class="col-md-6"><label class="form-label fw-semibold">Name</label><input class="form-control" value="{{ $professor->name }}" disabled></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Email</label><input class="form-control" value="{{ $professor->email }}" disabled></div>
                                </div>
                                @if($professor->advisory && $professor->advisory->gradeLevel)
                                    <div class="alert alert-success">Assigned as <strong>Grade {{ $professor->advisory->gradeLevel->name }} Adviser</strong> for {{ $professor->advisory->school_year }}</div>
                                @elseif($professor->advisory)
                                    <div class="alert alert-warning">Adviser assigned, but grade level missing</div>
                                @else
                                    <div class="alert alert-secondary">Not assigned as adviser</div>
                                @endif
                            </div>

                            <!-- Subjects -->
                            <div class="tab-pane fade" id="subjects{{ $professor->id }}">
                                <h6 class="fw-bold">Assigned Subjects</h6>
                                @if(isset($assignments[$professor->id]) && count($assignments[$professor->id]) > 0)
                                    <div class="assignments-list">
                                        @foreach($assignments[$professor->id] as $assignment)
                                            <div class="assignment-item d-flex justify-content-between align-items-center border-bottom py-2">
                                                <div>
                                                    <strong>{{ optional($assignment->subject)->name ?? 'Unknown Subject' }}</strong>
                                                    @if($assignment->gradeLevel)
                                                        <span class="text-muted">(Grade {{ $assignment->gradeLevel->name }})</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <form action="{{ route('admin.professors.removeAssignment', $assignment->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill d-flex align-items-center px-3" onclick="return confirm('Are you sure?')">
                                                            <i class="bi bi-trash"></i><span class="d-none d-sm-inline ms-2">Remove</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted">No assignments found.</p>
                                @endif
                                <button class="btn btn-success btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#assignModal{{ $professor->id }}"><i class="bi bi-journal-plus"></i> Assign Subject</button>
                            </div>

                            <!-- Adviser -->
                            <div class="tab-pane fade" id="adviser{{ $professor->id }}">
                                <form method="POST" action="{{ route('admin.professors.assignAdviser', $professor->id) }}">
                                    @csrf
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label>Grade Level</label>
                                            <select name="grade_level_id" class="form-select" required>
                                                @foreach($gradeLevels as $level)
                                                    @php
                                                        $assigned = $assignedGrades->where('grade_level_id', $level->id)->where('user_id', '!=', $professor->id)->count() > 0;
                                                    @endphp
                                                    <option value="{{ $level->id }}" {{ $professor->advisory && $professor->advisory->grade_level_id == $level->id ? 'selected' : '' }} {{ $assigned ? 'disabled' : '' }}>
                                                        Grade {{ $level->name }} {{ $assigned ? '(Already Assigned)' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>School Year</label>
                                            <input type="text" name="school_year" class="form-control" value="{{ $professor->advisory?->school_year ?? $currentSchoolYear }}" pattern="\d{4}-\d{4}" inputmode="numeric" placeholder="YYYY-YYYY" required>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">Each grade can only have one adviser per school year.</p>
                                    <button type="submit" class="btn btn-primary btn-sm mt-2">{{ $professor->advisory ? 'Update Adviser' : 'Assign Adviser' }}</button>
                                </form>
                                @if($professor->advisory)
                                    <form action="{{ route('admin.professors.removeAdviser', $professor->id) }}" method="POST" class="mt-2">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this adviser?');">Remove Adviser</button>
                                    </form>
                                @endif
                            </div>

                            <!-- Edit -->
                            <div class="tab-pane fade" id="edit{{ $professor->id }}">
                                <form method="POST" action="{{ route('admin.professors.update', $professor->id) }}">
                                    @csrf @method('PUT')
                                    <div class="row mb-3">
                                        <div class="col-md-6"><label>Name</label><input type="text" name="name" class="form-control" value="{{ $professor->name }}" required></div>
                                        <div class="col-md-6"><label>Email</label><input type="email" name="email" class="form-control" value="{{ $professor->email }}" required></div>
                                    </div>
                                    <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-save"></i> Save Changes</button>
                                </form>
                            </div>

                            <!-- Remove -->
                            <div class="tab-pane fade" id="remove{{ $professor->id }}">
                                <form method="POST" action="{{ route('admin.professors.destroy', $professor->id) }}">
                                    @csrf @method('DELETE')
                                    <p>Are you sure you want to remove <strong>{{ $professor->name }}</strong>? This action cannot be undone.</p>
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Remove Professor</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Subject Modal -->
        <div class="modal fade" id="assignModal{{ $professor->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.professors.assign') }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $professor->id }}">
                    <div class="modal-content shadow-sm rounded-3">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">Assign Subject</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Grade Level</label>
                                <select name="grade_level_id" id="gradeLevel{{ $professor->id }}" class="form-select" required>
                                    <option value="" disabled selected>Select grade</option>
                                    @foreach($gradeLevels as $level)
                                        <option value="{{ $level->id }}">{{ $level->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Subject</label>
                                <select name="subject_id" id="subject{{ $professor->id }}" class="form-select" required>
                                    <option value="" disabled selected>Select subject</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success"><i class="bi bi-plus-circle"></i> Assign</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

</div>
@endsection

@push('styles')
<style>
.professor-card:hover { transform: translateY(-2px); transition: all 0.2s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
.assignment-item:hover { background-color: #e6f4ea; transition: all 0.2s ease; border-radius: 0.375rem; }
.modal-header.bg-success { background-color: #198754 !important; }
</style>
@endpush

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('input[name="search"]');
    if(searchInput){ let typingTimer; searchInput.addEventListener('input', () => { clearTimeout(typingTimer); typingTimer = setTimeout(() => searchInput.form.submit(), 400); }); }

    const cardViewBtn = document.getElementById('cardViewBtn'), listViewBtn = document.getElementById('listViewBtn'), cardView = document.getElementById('cardView'), listView = document.getElementById('listView');
    cardViewBtn.addEventListener('click', () => { cardView.classList.remove('d-none'); listView.classList.add('d-none'); cardViewBtn.classList.add('active'); listViewBtn.classList.remove('active'); });
    listViewBtn.addEventListener('click', () => { cardView.classList.add('d-none'); listView.classList.remove('d-none'); listViewBtn.classList.add('active'); cardViewBtn.classList.remove('active'); });

    const subjectsByGrade = @json($subjectsGrouped);
    @foreach($professors as $professor)
        const gradeSelect{{ $professor->id }} = document.getElementById('gradeLevel{{ $professor->id }}');
        const subjectSelect{{ $professor->id }} = document.getElementById('subject{{ $professor->id }}');
        if(gradeSelect{{ $professor->id }}) gradeSelect{{ $professor->id }}.addEventListener('change', function() {
            subjectSelect{{ $professor->id }}.innerHTML = '<option value="" disabled selected>Select subject</option>';
            if(subjectsByGrade[this.value]) subjectsByGrade[this.value].forEach(s => { let o = document.createElement('option'); o.value = s.id; o.textContent = s.name; subjectSelect{{ $professor->id }}.appendChild(o); });
        });
    @endforeach
});
</script>
@endsection
