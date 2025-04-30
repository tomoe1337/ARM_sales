@extends('layouts.app')

@section('title', 'Отчет о рабочем времени')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Отчет о рабочем времени сотрудников</h1>
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
                                    <th>Сотрудник</th>
                                    <th>Дата</th>
                                    <th>Начало смены</th>
                                    <th>Конец смены</th>
                                    <th>Длительность</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sessions as $userSessions)
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 