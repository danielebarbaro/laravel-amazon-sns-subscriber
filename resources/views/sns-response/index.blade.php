@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xs-12 ">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        @lang('amazon.sns-list')
                    </div>
                    <div class="panel-body">
                        <table class="table table-responsive">
                            <thead>
                            <tr>
                                <th>@lang('default.type')</th>
                                <th>@lang('default.notification_type')</th>
                                <th>@lang('default.email')</th>
                                <th>@lang('default.date')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($sns_result as $response_result)
                                <tr>
                                    <td>
                                        <span class='badge badge-warning'>{{ $response_result->type }}</span>
                                    </td>
                                    <td>
                                        <span class='badge badge-danger'>{{ $response_result->type }}</span>
                                    </td>
                                    <td>
                                        <code>{{ $response_result->source_email }}</code>
                                    </td>
                                    <td>
                                        <span>{{ optional($response_result->datetime_payload)->formatLocalized('%d %B %Y - %H:%M') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr class="text-center">
                                    <td colspan="3">
                                        <h4>Nessun record.</h4>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-xs-12">
                                {{ $sns_result->links( "pagination::bootstrap-4") }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
