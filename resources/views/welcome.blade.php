<!DOCTYPE html>
<html>
<head>
    <title>Task Manager</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="http://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://cdn.bootcss.com/toastr.js/2.1.3/toastr.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/limonte-sweetalert2/7.19.1/sweetalert2.css" rel="stylesheet">
    <script src="http://cdn.bootcss.com/jquery/3.1.0/jquery.min.js"></script>
    <script src="http://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="http://cdn.bootcss.com/toastr.js/2.1.3/toastr.min.js"></script>
    <script src="https://cdn.bootcss.com/limonte-sweetalert2/7.19.1/sweetalert2.js"></script>
    </head>
<body>

<div class="container">

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>管理器
                        <a onclick="addForm()" class="btn btn-primary pull-right" style="margin-top: -8px;">添加任务</a>
                    </h4>
                </div>
                <div class="panel-body">
                    <table id="contact-table" class="table table-striped">
                        <thead>
                        <tr>
                            <th width="30">No</th>
                            <th>Name</th>
                            <th>content</th>
                            <th>created_at</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody id="task-list">
                        @foreach($tasks as $task)
                            <tr id="task{{ $task->id }}">
                                <td>{{ $task->id }}</td>
                                <td>{{ $task->name }}</td>
                                <td>{{ $task->content }}</td>
                                <td>{{ $task->created_at }}</td>
                                <td>
                                    <button  class="btn btn-info edit" onclick="editForm({{ $task->id }})" value="{{ $task->id }}">编辑</button>
                                    <button class="btn btn-warning delete" onclick="deleteData({{ $task->id }})" value="{{ $task->id }}">删除</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $tasks->links() !!}
                </div>
            </div>
        </div>
    </div>

    @include('from')

</div>
<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function addForm() {
        save_method = 'add';
        $('#modal-form').modal('show');
        $('.modal-title').text('添加任务');
        $('input[name=_method]').val('POST');
    }

    function deleteData(id) {
        swal({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: '#d33',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then(function () {
            $.ajax({
                url : "{{ url('task') }}" + '/' + id,
                type : "POST",
                data : {'_method' : 'DELETE'},
                success : function(data) {
                    if (data.success === true) {
                        $('#task'+id).remove();
                        swal({
                            title: 'Success!',
                            text: data.message,
                            type: 'success',
                            timer: '1500'
                        })
                    }
                },
                error : function () {
                    swal({
                        title: 'Oops...',
                        text: data.message,
                        type: 'error',
                        timer: '1500'
                    })
                }
            });
        })
    }

    function editForm(id) {
        save_method = 'edit';
        $('input[name=_method]').val('PATCH');
        $.ajax({
            url: "{{ url('task') }}" + '/' + id + "/edit",
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                $('#modal-form').modal('show');
                $('.modal-title').text('Edit Contact');

                $('#id').val(data.id);
                $('#name').val(data.name);
                $('#content').val(data.content);
            },
            error : function() {
                alert("Nothing Data");
            }
        });
    }

    $('#modal-form form').on('submit', function (e) {
        e.preventDefault();
        var id = $('#id').val();
        if (save_method == 'add') url = "{{ url('task') }}";
        else url = "{{ url('task') . '/' }}" + id;
        var data = {
            name: $('#name').val(),
            content: $('#content').val()
        };
        $.ajax({
            url : url,
            type : $('input[name=_method]').val(),
            data: data,
            dataType: 'json',
            success: function (data) {
                $('#modal-form').modal('hide');

                var task = '<tr id="task' + data.task.id + '">' +
                    '<td>'+ data.task.id + '</td>' +
                    '<td>'+ data.task.name + '</td>' +
                    '<td>'+ data.task.content + '</td>' +
                    '<td>'+ data.task.created_at + '</td>' +
                    '<td>' +
                    '<button  class="btn btn-info edit" onclick="editForm(' + data.task.id + ')" value="' + data.task.id +'">编辑</button> ' +
                    '<button class="btn btn-warning delete" onclick="deleteData(' + data.task.id + ')" value="' + data.task.id +'">删除</button> ' +
                    '</td>'
                    '<tr>';
                if (save_method == 'add'){
                    $('#task-list').prepend(task);

                    toastr.success('添加成功！');
                } else {
                    $('#task'+data.task.id).replaceWith(task);
                    toastr.success('编辑成功！');
                }

            },
            error : function (msg) {
                if (msg.status == 422) {
                    var json=JSON.parse(msg.responseText);
                    json = json.errors;

                    for (index in json) {
                        $("#" + index).parent().addClass('has-error')
                        $("#" + index).parent().find("span").text(json[index]);
                    }
                    var errorsHtml= '';
                    $.each( json, function( key, value ) {
                        errorsHtml += '<li>' + value[0] + '</li>';
                    });
                    toastr.error( errorsHtml , "Error " + msg.status);
                } else {
                    alert('服务器连接失败');
                    return ;
                }
            }
        })
    })
</script>


</body>
</html>
