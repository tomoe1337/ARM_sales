@extends('layouts.app')

@section('title', 'Отчет о рабочем времени')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            @if(isset($user))
                <h1>Отчет о рабочем времени: {{ $user->name }}</h1>
            @else
                <h1>Отчет о рабочем времени сотрудников</h1>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    @if(!isset($user))
                                    <th>Сотрудник</th>
                                    @endif
                                    <th>Дата</th>
                                    <th>Начало смены</th>
                                    <th>Конец смены</th>
                                    <th>Длительность</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sessions as $userSessions)
                                    @if(isset($user)) {{-- If $user is set, $sessions is likely a collection for one user --}}
                                        {{-- Loop directly through sessions as they are already filtered --}}
                                        @foreach($sessions as $session)
                                            <tr>
                                                <td>{{ $session->start_time->format('d.m.Y') }}</td>
                                                <td>{{ $session->start_time->format('H:i') }}</td>
                                                <td>{{ $session->end_time ? $session->end_time->format('H:i') : 'В процессе' }}</td>
                                                <td>
                                                    @if($session->end_time)
                                                        {{ $session->start_time->diff($session->end_time)->format('%H:%I') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        @break {{-- Break the outer loop after processing the single user's sessions --}}
                                    @else {{-- If $user is not set, loop through grouped sessions --}}
                                        @foreach($userSessions as $session)
                                            <tr>
                                                <td>{{ $session->user->name }}</td>
                                                <td>{{ $session->start_time->format('d.m.Y') }}</td>
                                                <td>{{ $session->start_time->format('H:i') }}</td>
                                                <td>{{ $session->end_time ? $session->end_time->format('H:i') : 'В процессе' }}</td>
                                                <td>
                                                    @if($session->end_time)
                                                        {{ $session->start_time->diff($session->end_time)->format('%H:%I') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 