<script>

    function loading(id) {
        $("#" + id).text("Processing.....");
        $("#" + id).attr("disabled", true);
    }

    function unloading(id, text) {
        $("#" + id).text(text);
        $("#" + id).removeAttr("disabled");
    }


    function pemisah_ribuan(textname, targetname) {
        var nominal_text = $(textname).val();
        var nominal = nominal_text.replaceAll(".", "");
        $(targetname).val(nominal);
        var angka_real = $(targetname).val();
        var attr_angka = formatAngka(angka_real);
        $(textname).val(attr_angka);
    }

    function formatAngka(angka, prefix) {
        var number_string = angka.toString().replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        // tambahkan titik jika yang di input sudah menjadi angka ribuan
        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? '' + rupiah : '');
    }
</script>

@if ($view == 'user-list')
    <script>
        $("#user_active").change(function() {
            var nilai = $(this).val();
            init_data_table(nilai);
        });

        init_data_table("");

        function pdfData(id) {
            window.open('{{ url('backdata/print_form_permohonan') }}' + '/' + id, '_blank');
        }


        $('.profile-image').click(function() {
            $('#image').trigger('click');
        });

        $("#image").change(function() {
            document.getElementById('profile-image').src = window.URL.createObjectURL(this.files[0]);
            $("#remove-profile-image").show();
        });

        function remove_foto() {
            $("#image").val(null);
            $("#profile-image").attr('src', '{{ asset('template/images/profil_icon.png') }}');
            $("#remove-profile-image").hide();
        }

        function init_data_table(is_active) {
            $("#listTable").dataTable().fnDestroy();
            var csrf_token = $('meta[name="csrf-token"]').attr('content');
            var table = $('#listTable').DataTable({
                processing: true,
                serverSide: true,
                dom: 'Blfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All'],
                ],
                // ajax: "{{ route('user.list') }}",
                ajax: {
                    type: "POST",
                    url: "{{ route('user.list') }}",
                    data: {
                        "is_active": is_active,
                        '_token': csrf_token
                    }
                },
                order: [
                    [0, "desc"]
                ],
                columns: [{
                        data: 'id',
                        name: 'id',
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'level',
                        name: 'level'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'birthday',
                        name: 'birthday'
                    },
                    {
                        data: 'last_payment',
                        name: 'last_payment'
                    },
                    {
                        data: 'user',
                        name: 'user'
                    },
                    {
                        data: 'blok',
                        name: 'blok'
                    },
                    {
                        data: 'nomor_rumah',
                        name: 'nomor_rumah'
                    },
                    {
                        data: 'foto',
                        name: 'foto'
                    },
                    {
                        data: 'username',
                        name: 'username'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'no_hp',
                        name: 'no_hp'
                    },

                ]
            });
        }



        function addData() {
            resetForm();

            save_method = "add";
            $('input[name=_method]').val('POST');
            $(".modal-title").text("Add Data");
            $("#modal-tambah").modal("show");
        }

        $("#form-tambah").submit(function(e) {
            loading("btn-save-data");
            e.preventDefault();
            var id = $('#id').val();
            if (save_method == "add") url = "{{ url('/backdata/user') }}";
            else url = "{{ url('/backdata/user') . '/' }}" + id;
            $.ajax({
                url: url,
                type: "POST",
                data: new FormData($('#modal-tambah form')[0]),
                contentType: false,
                processData: false,
                success: function(data) {
                    unloading("btn-save-data", "Save");
                    if (data.success) {
                        $('#modal-tambah').modal('hide');
                        reloadTable();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: data.message,
                            showConfirmButton: false,
                            scrollbarPadding: false,
                        });
                    }
                }

            });
        });

        function editData(id) {
            save_method = "edit";
            $('input[name=_method]').val('PATCH');
            $.ajax({
                url: "{{ url('/backdata/user') }}" + "/" + id + "/edit",
                type: "GET",
                dataType: "JSON",
                success: function(data) {
                    $('#modal-tambah').modal("show");
                    $('.modal-title').text("Edit Data");
                    $('#id').val(data.id);
                    $("#name").val(data.name);
                    $("#username").val(data.username);
                    $("#email").val(data.email);
                    $("#password").val("");
                    $("#jenis_kelamin").val(data.jenis_kelamin);
                    $("#no_hp").val(data.no_hp);
                    $("#level").val(data.level);
                    $("#is_active").val(data.is_active);
                    $("#penyelia").val(data.penyelia);
                    $("#blok").val(data.blok);
                    $("#nomor_rumah").val(data.nomor_rumah);
                    $("#daya_listrik").val(data.daya_listrik);
                    $("#luas_tanah").val(data.luas_tanah);
                    $("#iuran_bulanan").val(data.iuran_bulanan);
                    $("#whatsapp_emergency").val(data.whatsapp_emergency);
                    $("#keterangan").val(data.keterangan);
                    $("#alamat_surat_menyurat").val(data.alamat_surat_menyurat);
                    $("#nomor_telepon_rumah").val(data.nomor_telepon_rumah);
                    $("#id_pelanggan_pdam").val(data.id_pelanggan_pdam);
                    $("#nomor_meter_pln").val(data.nomor_meter_pln);
                    $("#mulai_menempati").val(data.mulai_menempati);
                    $("#birthday").val(data.birthday);
                    if (data.foto != null && data.foto != '') {
                        $("#profile-image").attr('src', '{{ asset('storage/profile') }}/' + data.foto);
                    } else {
                        $("#profile-image").attr('src', '{{ asset('template/images/profil_icon.png') }}');
                    }


                }
            })
        }

        function deleteData(id) {
            Swal.fire({
                icon: 'question',
                title: 'Delete this data?',

                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    var csrf_token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: "{{ url('/backdata/user') }}" + '/' + id,
                        type: "POST",
                        data: {
                            '_method': 'DELETE',
                            '_token': csrf_token
                        },
                        success: function($data) {
                            reloadTable();
                        }
                    });
                }
            });
        }

        function detailData(id) {
            $.ajax({
                url: "{{ url('backdata/user') }}" + "/" + id,
                type: "GET",
                success: function(data) {
                    $("#detail-content").html(data);
                    $("#modal-detail").modal("show");
                }
            });
        }

        $("#btn-print-detail").click(function() {
            var id = $("#id-detail").val();
            window.open('{{ url('backdata/print_detail') }}' + '/' + id, '_blank');

        });

        function resetForm() {
            $("#name").val("");
            $("#username").val("");
            $("#email").val("");
            $("#password").val("");
            $("#jenis_kelamin").val("");
            $("#no_hp").val("");
            $("#level").val("");
            $("#is_active").val("");
            $("#penyelia").val("");
            $("#blok").val("");
            $("#nomor_rumah").val("");
            $("#daya_listrik").val("");
            $("#luas_tanah").val("");
            $("#iuran_bulanan").val("");
            $("#whatsapp_emergency").val("");
            $("#keterangan").val("");
            $("#alamat_surat_menyurat").val("");
            $("#nomor_telepon_rumah").val("");
            $("#id_pelanggan_pdam").val("");
            $("#nomor_meter_pln").val("");
            $("#mulai_menempati").val("");
            $("#image").val(null);
            $("#birthday").val("");
            $("#profile-image").attr('src', '{{ asset('template/images/profil_icon.png') }}');
            $("#remove-profile-image").hide();
        }



        $("#btn-import-user").click(function() {
            $("#file").val(null);
            $("#modal-import").modal('show');
        });


        $("#btn-bulk-update").click(function() {
            $("#file_edit").val(null);
            $("#modal-bulk-edit").modal('show');
        });


        $("#modal-import form").submit(function(e) {
            e.preventDefault();
            $("#btn-import-excel").text("importing data....");
            $("#btn-import-excel").attr("disabled", true);
            $.ajax({
                url: "{{ route('user.upload') }}",
                type: "POST",
                data: new FormData($('#modal-import form')[0]),
                contentType: false,
                processData: false,
                success: function(data) {

                    console.log(data);

                    if (data.success) {
                        $("#modal-import").modal("hide");
                        $("#file").val(null);
                        reloadTable();
                        Swal.fire({
                            icon: 'success',
                            title: data.message,
                            html: data.failed,
                            showConfirmButton: false,
                            scrollbarPadding: false,
                        });
                        $("#btn-import-excel").text("import");
                        $("#btn-import-excel").removeAttr("disabled");

                    } else {
                        $("#modal-import").modal("hide");
                        $("#file").val(null);
                        reloadTable();
                        Swal.fire({
                            icon: 'error',
                            title: data.message,
                            showConfirmButton: false,
                            scrollbarPadding: false,
                        });
                        $("#btn-import-excel").text("import");
                        $("#btn-import-excel").removeAttr("disabled");
                    }
                }
            })
        });


        $("#modal-bulk-edit form").submit(function(e) {
            e.preventDefault();
            $("#btn-import-update").text("importing data....");
            $("#btn-import-update").attr("disabled", true);
            $.ajax({
                url: "{{ route('user.upload.update') }}",
                type: "POST",
                data: new FormData($('#modal-bulk-edit form')[0]),
                contentType: false,
                processData: false,
                success: function(data) {

                    console.log(data);

                    if (data.success) {
                        $("#modal-bulk-edit").modal("hide");
                        $("#file_edit").val(null);
                        reloadTable();
                        Swal.fire({
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            scrollbarPadding: false,
                        });
                        $("#btn-import-update").text("import");
                        $("#btn-import-update").removeAttr("disabled");

                    } else {
                        $("#modal-bulk-edit").modal("hide");
                        $("#file_edit").val(null);
                        reloadTable();
                        Swal.fire({
                            icon: 'error',
                            title: data.message,
                            showConfirmButton: false,
                            scrollbarPadding: false,
                        });
                        $("#btn-import-update").text("import");
                        $("#btn-import-update").removeAttr("disabled");
                    }
                }
            })
        });


        function reloadTable() {
            var table = $("#listTable").DataTable();
            table.ajax.reload(null, false);
        }


        function download_template_upload() {
            window.open("{{ asset('assets/file/template_import_user_dianistana.xlsx') }}", "_blank");
        }

        function download_template_edit() {
            window.location = "{{ url('backdata/download-template-edit') }}";
        }
    </script>
@endif
@if ($view == 'transaction')
    <script>
        init_data_table("", "", "", "", "", "transaksi");

        $("#btn-print").click(function() {
            var awal = $("#awal").val();
            var akhir = $("#akhir").val();
            var payment = $("#payment").val();
            var unit = $("#bisnis_unit").val();
            var payment_status = $("#payment_status").val();
            var pilih = $('input[name="date_selection"]:checked').val();

            if (payment == '') {
                payment = '0';
            }

            if (unit == '') {
                unit = '0';
            }

            if (payment_status == '') {
                payment_status = '0';
            }

            if (pilih == '') {
                pilih = '0';
            }

            if (awal == '' || akhir == '') {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal Awal atau Tanggal Akhir Tidak Boleh Kosong...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else if (awal > akhir) {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal tidak valid...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else {

                window.open("{{ url('backdata/print_data_transaction') }}" + "/" + awal + "/" + akhir + "/" +
                    payment + "/" + unit + "/" + payment_status + "/" + pilih, '_blank');
            }
        });



        $("#btn-filter").click(function() {
            var awal = $("#awal").val();
            var akhir = $("#akhir").val();
            var payment = $("#payment").val();
            var unit = $("#bisnis_unit").val();
            var payment_status = $("#payment_status").val();
            var pilih = $('input[name="date_selection"]:checked').val();

            if (awal == '' || akhir == '') {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal Awal atau Tanggal Akhir Tidak Boleh Kosong...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else if (awal > akhir) {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal tidak valid...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else {

                init_data_table(awal, akhir, payment, unit, payment_status, pilih);
            }
        });


        function init_data_table(awal, akhir, payment, unit, payment_status, pilih) {

            $("#listTable").dataTable().fnDestroy();
            var csrf_token = $('meta[name="csrf-token"]').attr('content');

            var table = $('#listTable').DataTable({
                processing: true,
                serverSide: true,
                dom: 'Blfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All'],
                ],
                ajax: {
                    type: "POST",
                    url: "{{ route('transaction.list') }}",
                    data: {
                        "awal": awal,
                        "akhir": akhir,
                        "payment": payment,
                        "unit": unit,
                        "payment_status": payment_status,
                        "pilih": pilih,
                        '_token': csrf_token
                    }
                },
                order: [
                    [0, "desc"]
                ],
                columns: [{
                        data: 'id',
                        name: 'id',
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'user_id',
                        name: 'user_id'
                    },
                    {
                        data: 'business_unit_id',
                        name: 'business_unit_id'
                    },
                    {
                        data: 'invoice',
                        name: 'invoice'
                    },
                    {
                        data: 'detail',
                        name: 'detail'
                    },
                    {
                        data: 'total_price',
                        name: 'total_price'
                    },
                    {
                        data: 'package_name',
                        name: 'package_name'
                    },

                    {
                        data: 'paid_at',
                        name: 'paid_at'
                    },

                ]
            });
        }


        function cancelData(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Cancel this booking transaction ?',
                input: "textarea",
                inputPlaceholder: "Reason cancelation...",
                inputAttributes: {
                    "aria-label": "Reason cancelation"
                },
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.isConfirmed) {
                    var csrf_token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: "{{ url('/backdata/transaction_cancel') }}",
                        type: "POST",
                        data: {
                            'id': id,
                            'text': $(".swal2-textarea").val(),
                            '_token': csrf_token
                        },
                        success: function($data) {
                            reloadTable();
                        }
                    });
                }
            });
        }




        function deleteData(id) {
            Swal.fire({
                icon: 'question',
                title: 'Delete this transaction ?',

                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    var csrf_token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: "{{ url('/backdata/transaction') }}" + '/' + id,
                        type: "POST",
                        data: {
                            '_method': 'DELETE',
                            '_token': csrf_token
                        },
                        success: function($data) {
                            reloadTable();
                        }
                    });
                }
            });
        }

        function detailData(id) {
            $.ajax({
                url: "{{ url('backdata/transaction') }}" + "/" + id,
                type: "GET",
                success: function(data) {
                    $("#detail-content").html(data);
                    $("#modal-detail").modal("show");
                }
            });
        }

        $("#btn-print-detail").click(function() {
            var id = $("#id-transaction").val();
            window.open('{{ url('backdata/print_transaction') }}' + '/' + id, '_blank');

        });

        function paymentData(id) {
            Swal.fire({
                icon: 'question',
                title: 'Process This Payment...?',

                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Process',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    var csrf_token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: "{{ url('backdata/booking_payment') }}",
                        type: "POST",
                        data: {
                            'id': id,
                            '_token': csrf_token
                        },
                        success: function(data) {
                            if (data.success) {
                                reloadTable();
                            }

                        }
                    });
                }
            });
        }


        function printData(id) {
            window.location = "{{ url('/backdata/booking_print_ticket') }}" + "/" + id;
        }

        function reloadTable() {
            var table = $('#listTable').DataTable();
            table.ajax.reload(null, false);
        }
    </script>
@endif
@if ($view == 'ticketing')
    <script>
        init_table("", "", "");

        function init_table(department, priority, status) {
            $("#listTable").dataTable().fnDestroy();
            var csrf_token = $('meta[name="csrf-token"]').attr('content');
            var table = $('#listTable').DataTable({
                processing: true,
                serverSide: true,
                dom: 'Blfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All'],
                ],
                ajax: {
                    type: "POST",
                    url: "{{ route('ticketing.list') }}",
                    data: {
                        "department": department,
                        "priority": priority,
                        "status": status,
                        '_token': csrf_token
                    }
                },

                order: [
                    [0, "desc"]
                ],
                columns: [{
                        data: 'id',
                        name: 'id',
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'ticket_number',
                        name: 'ticket_number'
                    },
                    {
                        data: 'user_id',
                        name: 'user_id'
                    },
                    {
                        data: 'subject',
                        name: 'subject'
                    },
                    {
                        data: 'department',
                        name: 'department'
                    },
                    {
                        data: 'priority',
                        name: 'priority'
                    },
                    {
                        data: 'document',
                        name: 'document'
                    },

                    {
                        data: 'updated_at',
                        name: 'updated_at'
                    },

                ]
            });
        }


        function filter_ticketing_data() {
            var department = $("#department-filter").val();
            var priority = $("#priority-filter").val();
            var status = $("#status-filter").val();
            init_table(department, priority, status);
        }


        $("#btn-on-hold").click(function() {
            var id = $("#ticket_id").val();
            Swal.fire({
                icon: 'question',
                title: 'Set This Ticket to On Hold ?',

                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Set to On Hold',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    var csrf_token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: "{{ url('/backdata/set_on_hold') }}",
                        type: "POST",
                        data: {
                            'id': id,
                            '_token': csrf_token
                        },
                        success: function(data) {
                            if (data.success) {
                                init_table("", "", "");
                                Swal.fire({
                                    icon: 'success',
                                    title: data.message,
                                    showConfirmButton: false,
                                    scrollbarPadding: false,
                                });
                                $("#modal-detail").modal("hide");
                            }

                        }
                    });
                }
            });
        });


        $("#btn-resolved").click(function() {
            var id = $("#ticket_id").val();
            Swal.fire({
                icon: 'question',
                title: 'Set This Ticket to Resolved ?',

                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Set to Resolved',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    var csrf_token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: "{{ url('/backdata/set_resolved') }}",
                        type: "POST",
                        data: {
                            'id': id,
                            '_token': csrf_token
                        },
                        success: function(data) {
                            if (data.success) {
                                init_table("", "", "");
                                Swal.fire({
                                    icon: 'success',
                                    title: data.message,
                                    showConfirmButton: false,
                                    scrollbarPadding: false,
                                });
                                $("#modal-detail").modal("hide");
                            }

                        }
                    });
                }
            });
        });


        $(document).ready(function() {
            $("#form-reply").submit(function(e) {
                loading("btn-post-reply");
                e.preventDefault();
                var message = CKEDITOR.instances.message.getData();
                var formdata = new FormData($('#modal-detail form')[0]);
                formdata.append('pesan', message);
                $.ajax({
                    url: "{{ url('backdata/ticketing') }}",
                    type: "POST",
                    data: formdata,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        unloading("btn-post-reply", "Post");
                        if (data.success) {
                            $('#modal-detail').modal('hide');
                            init_table("", "", "");
                            Swal.fire({
                                icon: 'success',
                                title: data.message,
                                showConfirmButton: false,
                                scrollbarPadding: false,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: data.message,
                                showConfirmButton: false,
                                scrollbarPadding: false,
                            });
                        }
                    }

                });
            });

        });




        function deleteData(id) {
            Swal.fire({
                icon: 'question',
                title: 'Delete this Ticket ?',

                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    var csrf_token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: "{{ url('/backdata/ticketing') }}" + '/' + id,
                        type: "POST",
                        data: {
                            '_method': 'DELETE',
                            '_token': csrf_token
                        },
                        success: function($data) {
                            init_table("", "", "");
                        }
                    });
                }
            });
        }

        function detailData(id) {
            $.ajax({
                url: "{{ url('backdata/ticketing') }}" + "/" + id,
                type: "GET",
                success: function(data) {
                    $(".modal-title").text("Ticketing Summary");
                    $("#detail-content").html(data);
                    $("#modal-detail").modal("show");
                    CKEDITOR.replace('message');
                }
            });
        }

        function open_payment() {
            var id = $("#ticket_user_id").val();
            $.ajax({
                url: "{{ url('backdata/payment_ticketing_list') }}" + "/" + id,
                type: "GET",
                success: function(data) {
                    console.log(data);
                    $("#modal-payment-ticketing").modal("show");
                    $("#payment-content").html(data);
                    $("#table-payment-ticketing").DataTable();
                    $("#payment_dedication").val(id);
                }
            })

        }

        function copy_payment_link(id) {

            navigator.clipboard.writeText("{{ url('payment_link_share') }}" + "/" + id).then(function() {
                alert('Payment link copied...');
                var ckeditor = CKEDITOR.instances.message.getData();

                CKEDITOR.instances.message.setData(ckeditor +
                    '<br><a class="payment-links" href="{{ url('payment_link_share') }}/' + id +
                    '"><span style="color:blue;text-decoration:underline;">Please Click this Payment Link to Pay</span></a>'
                );
            }, function() {
                alert('Failure to copy. Check permissions for clipboard')
            });
        }


        function copy_payment_link2(id) {
            var token = $("#token_"+id).val();
            navigator.clipboard.writeText("{{ url('payment_link_external') }}" + "/" + token).then(function() {
                alert('Payment link copied...');
            }, function() {
                alert('Failure to copy. Check permissions for clipboard')
            });
        }

        $("#new_payment_text").click(function() {
            $("#form-section").slideDown();
            $("#payment-content").slideUp();
            $(this).hide();
            $("#cancel_payment_text").show();
            reset_data();
        })

        function reset_data() {
            $("#payment_name").val("");
            $("#payment_desc").val("");
            $("#payment_type").val("");
            $("#due_date").val("");
            $("#periode").val("");
            $("#payment_amount").val("");
        }

        $("#cancel_payment_text").click(function() {
            $("#form-section").slideUp();
            $("#payment-content").slideDown();
            $(this).hide();
            $("#new_payment_text").show();
        })

        $("#form-payment-ticketing").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ url('backdata/add_ticketing_payment') }}",
                type: "POST",
                dataType: "JSON",
                data: $(this).serialize(),
                success: function(data) {
                    if (data.success) {
                        $("#form-section").slideUp();
                        $("#payment-content").slideDown();
                        $("#cancel_payment_text").hide();
                        $("#new_payment_text").show();
                        open_payment();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: data.message,
                            showConfirmButton: false,
                            scrollbarPadding: false,
                        });
                    }
                }
            })
        });
    </script>
@endif
@if ($view == 'pembayaran-list')
    <script>
        $(document).ready(function() {
            $("#payment_dedication").select2({
                theme: "classic",
                dropdownParent: $("#modal-tambah .modal-content")
            });
            $("#payment_dedication_admin").select2({
                theme: "classic",
                dropdownParent: $("#modal-payment .modal-content")
            });
        })

        $('.profile-image').click(function() {
            $('#image').trigger('click');
        });

        $("#image").change(function() {
            document.getElementById('profile-image').src = window.URL.createObjectURL(this.files[0]);
            $("#remove-profile-image").show();
        });

        function remove_foto() {
            $("#image").val(null);
            $("#profile-image").attr('src', '{{ asset('template/images/profil_icon.png') }}');
            $("#remove-profile-image").hide();
        }

        var table = $('#listTable').DataTable({
            processing: true,
            serverSide: true,
            dom: 'Blfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All'],
            ],
            ajax: "{{ route('pembayaran.list') }}",
            order: [
                [0, "desc"]
            ],
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'payment_name',
                    name: 'payment_name'
                },
                {
                    data: 'payment_type',
                    name: 'payment_type'
                },
                {
                    data: 'due_date',
                    name: 'due_date'
                },
                {
                    data: 'periode',
                    name: 'periode'
                },
                {
                    data: 'payment_amount',
                    name: 'payment_amount'
                },
                {
                    data: 'payment_dedication',
                    name: 'payment_dedication'
                },
                {
                    data: 'status',
                    name: 'status'
                },

            ]
        });

        function addData() {
            resetForm();

            save_method = "add";
            $('input[name=_method]').val('POST');
            $(".modal-title").text("Add Data");
            $("#modal-tambah").modal("show");
        }
        
        

        $("#form-tambah").submit(function(e) {
            loading("btn-save-data");
            e.preventDefault();
            var id = $('#id').val();
            if (save_method == "add") url = "{{ url('/backdata/pembayaran') }}";
            else url = "{{ url('/backdata/pembayaran') . '/' }}" + id;
            $.ajax({
                url: url,
                type: "POST",
                data: new FormData($('#modal-tambah form')[0]),
                contentType: false,
                processData: false,
                success: function(data) {
                    unloading("btn-save-data", "Save");
                    if (data.success) {
                        $('#modal-tambah').modal('hide');
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: data.message,
                            showConfirmButton: false,
                            scrollbarPadding: false,
                        });
                    }
                }

            });
        });

        function editData(id) {
            save_method = "edit";
            $('input[name=_method]').val('PATCH');
            $.ajax({
                url: "{{ url('/backdata/pembayaran') }}" + "/" + id + "/edit",
                type: "GET",
                dataType: "JSON",
                success: function(data) {
                    $('#modal-tambah').modal("show");
                    $('.modal-title').text("Edit Data");
                    $('#id').val(data.id);
                    $("#payment_name").val(data.payment_name);
                    $("#payment_desc").val(data.payment_desc);
                    $("#payment_type").val(data.payment_type);
                    $("#due_date").val(data.due_date);
                    $("#periode").val(data.periode);
                    $("#payment_amount").val(data.payment_amount);
                    $("#payment_amount_text").val(formatAngka(data.payment_amount, ''));
                    $("#payment_dedication").val(data.payment_dedication).trigger('change');


                }
            })
        }

        function deleteData(id) {
            Swal.fire({
                icon: 'question',
                title: 'Delete this data?',

                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    var csrf_token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: "{{ url('/backdata/pembayaran') }}" + '/' + id,
                        type: "POST",
                        data: {
                            '_method': 'DELETE',
                            '_token': csrf_token
                        },
                        success: function($data) {
                            table.ajax.reload(null, false);
                        }
                    });
                }
            });
        }

        function detailData(id) {
            $.ajax({
                url: "{{ url('backdata/pembayaran') }}" + "/" + id,
                type: "GET",
                success: function(data) {
                    $("#detail-content").html(data);
                    $("#modal-detail").modal("show");
                }
            });
        }

        function print_kwitansi(id) {
            window.open('{{ url('backdata/kwitansi') }}' + '/' + id, '_blank');
        }

        function resetForm() {
            $("#payment_name").val("");
            $("#payment_desc").val("");
            $("#payment_type").val("");
            $("#due_date").val("");
            $("#periode").val("");
            $("#payment_amount").val("");
            $("#payment_dedication").val("").trigger('change');

        }

        function delete_payment(id) {
            Swal.fire({
                icon: 'question',
                title: 'Delete this Payment...?',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    var csrf_token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: "{{ url('backdata/delete_payment') }}",
                        type: "POST",
                        data: {
                            'id': id,
                            '_token': csrf_token
                        },
                        success: function(data) {
                            $("#modal-detail").modal("hide");
                            detailData(data);
                        }
                    });
                }
            });
        }


        function paymentData(id) {
            $.ajax({
                url: "{{ url('backdata/payment_admin') }}" + "/" + id,
                type: "GET",
                dataType: "JSON",
                success: function(data) {
                    $("#modal-payment").modal("show");
                    $("#payment_id_admin").val(data.id);
                    $("#payment_name_admin").val(data.payment_name);
                    $("#payment_type_hidden").val(data.payment_type);
                    if (data.payment_type == 1) {
                        $("#payment_type_admin").val("Iuran Bulanan Komplek");
                        $("#payment_amount_admin").val("");

                    } else if (data.payment_type == 2) {
                        $("#payment_type_admin").val("Iuran Rutin");
                        $("#payment_amount_admin").val(data.payment_amount);

                    } else if (data.payment_type == 3) {
                        $("#payment_type_admin").val("Sekali Bayar");
                        $("#payment_amount_admin").val(data.payment_amount);
                    }
                    $("#payment_dedication_admin").val(data.payment_dedication).trigger('change');
                    if (data.payment_dedication < 0) {
                        $("#payment_dedication_admin").removeAttr("disabled");
                    } else {
                        $("#payment_dedication_admin").attr("disabled", true);
                    }

                }
            })


        }


        $("#payment_dedication_admin").change(function() {

            var nilai = $(this).val();
            var ptype = $("#payment_type_hidden").val();
            if (ptype > 1) {

            } else {
                $.ajax({
                    url: "{{ url('backdata/get_iuran_bulanan') }}" + "/" + nilai,
                    type: "GET",
                    dataType: "JSON",
                    success: function(data) {
                        $("#payment_amount_admin").val(data);
                    }
                });
            }

        });

        $("#form-payment-admin").submit(function(e) {
            var id = $("#payment_id_admin").val();
            e.preventDefault();
            $.ajax({
                url: "{{ url('backdata/process_payment') }}",
                type: "POST",
                datType: "JSON",
                data: $(this).serialize(),
                success: function(data) {
                    console.log(data);
                    if (data.success) {
                        $("#modal-payment").modal("hide");
                        detailData(id);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: data.message,
                            showConfirmButton: false,
                            scrollbarPadding: false,
                        });
                    }

                }
            })
        });

        function copyData(id) {
            navigator.clipboard.writeText("{{ url('payment_link_share') }}" + "/" + id).then(function() {
                alert('Payment link copied...');
            }, function() {
                alert('Failure to copy. Check permissions for clipboard')
            });
        }


        function copyData2(id) {
            var token = $("#payment_token_" + id).val();
            navigator.clipboard.writeText("{{ url('payment_link_external') }}" + "/" + token).then(function() {
                alert('Payment link External copied...');
            }, function() {
                alert('Failure to copy. Check permissions for clipboard')
            });
        }



        function ribuan() {
            pemisah_ribuan("#payment_amount_text", "#payment_amount");
        }
    </script>
@endif
@if ($view == 'broadcasting')
    <script>
        $(document).ready(function() {
            $(".sending-time").css("display", "none");

            $("#user_id").select2({
                theme: "classic",
                dropdownParent: $("#modal-tambah .modal-content")
            });

            $("#person").select2({
                theme: "classic",
                dropdownParent: $("#modal-tambah .modal-content")
            });

            $("#sending_priority").change(function() {
                var nilai = $(this).val();
                if (nilai == "1") {
                    $(".sending-time").css("display", "none");
                } else {
                    $(".sending-time").css("display", "block");
                }
            });
        })


        var table = $('#listTable').DataTable({
            processing: true,
            serverSide: true,
            dom: 'Blfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All'],
            ],
            ajax: "{{ route('broadcasting.list') }}",
            order: [
                [0, "desc"]
            ],
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'sending_status',
                    name: 'sending_status'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'message',
                    name: 'message'
                },
                {
                    data: 'image',
                    name: 'name'
                },
                {
                    data: 'admin_id',
                    name: 'admin_id'
                },
                {
                    data: 'user_id',
                    name: 'user_id'
                },
                {
                    data: 'send_date',
                    name: 'send_date'
                },


            ]
        });

        function addData() {
            resetForm();

            save_method = "add";
            $('input[name=_method]').val('POST');
            $(".modal-title").text("Add Data");
            $("#modal-tambah").modal("show");
        }

        $("#form-tambah").submit(function(e) {
            loading("btn-save-data");
            e.preventDefault();
            var id = $('#id').val();

            if (save_method == "add") url = "{{ url('/backdata/broadcasting') }}";
            else url = "{{ url('/backdata/broadcasting') . '/' }}" + id;
            $.ajax({
                url: url,
                type: "POST",
                data: new FormData($('#modal-tambah form')[0]),
                contentType: false,
                processData: false,
                success: function(data) {
                    unloading("btn-save-data", "Save");
                    if (data.success) {
                        $('#modal-tambah').modal('hide');
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: data.message,
                            showConfirmButton: false,
                            scrollbarPadding: false,
                        });
                    }
                }

            });
        });

        function editData(id) {
            save_method = "edit";
            $('input[name=_method]').val('PATCH');
            $.ajax({
                url: "{{ url('/backdata/broadcasting') }}" + "/" + id + "/edit",
                type: "GET",
                dataType: "JSON",
                success: function(data) {
                    $('#modal-tambah').modal("show");
                    $('.modal-title').text("Edit Data");
                    $('#id').val(data.data.id);
                    $("#title").val(data.data.title);
                    $("#image").val(null);

                    $("#message").val(data.data.message);
                    $("#send_date").val(data.data.send_date);
                    $("#send_time").val(data.data.send_time);
                    if (data.data.is_blok == 1) {
                        $("#user_id").val(-2).trigger('change');
                        $("#blok").val(data.data.user_id);
                    } else {
                        $("#user_id").val(data.data.user_id).trigger('change');
                        if (data.data.user_id == -3) {
                            $("#person").val(data.person).trigger('change');
                        }

                    }

                    if (data.data.sending_priority == 2) {
                        $(".sending-time").css("display", "block");
                    } else {
                        $(".sending-time").css("display", "none");
                    }



                }
            })
        }

        function deleteData(id) {
            Swal.fire({
                icon: 'question',
                title: 'Delete this data?',

                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    var csrf_token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: "{{ url('/backdata/broadcasting') }}" + '/' + id,
                        type: "POST",
                        data: {
                            '_method': 'DELETE',
                            '_token': csrf_token
                        },
                        success: function($data) {
                            table.ajax.reload(null, false);
                        }
                    });
                }
            });
        }


        function resetForm() {
            $("#title").val("");
            $("#message").val("");
            $("#user_id").val("").trigger('change');
            $("#password").val("");
            $("#blok").val("");
            $("#send_date").val("");
            $("#image").val(null);

        }

        $("#user_id").change(function() {
            var userid = $(this).val();
            if (userid == -2) {
                $("#blok-container").slideDown();
                $("#blok").val("");
                $("#person-container").slideUp();
                $("#person").val("");
            } else if (userid == -3) {
                $("#person-container").slideDown();
                $("#person").val("");
                $("#blok-container").slideUp();
                $("#blok").val("");
            } else {
                $("#blok-container").slideUp();
                $("#blok").val("");

                $("#person-container").slideUp();
                $("#person").val("");
            }
        });

        function check_broadcasting() {
            setTimeout(function() {
                $.ajax({
                    url: "{{ url('backdata/check_broadcasting') }}",
                    type: "GET",
                    dataType: 'JSON',
                    success: function(data) {
                        console.log(data)
                        table.ajax.reload(null, false);
                    },
                    complete: check_broadcasting
                });
            }, 5000);
        }
        check_broadcasting();
    </script>
@endif

@if ($view == 'report-iuran')
    <script>
        init_data_table("", "", "", "");

        function init_data_table(awal, akhir, payment, penyelia) {
            $("#report-table").dataTable().fnDestroy();

            var csrf_token = $('meta[name="csrf-token"]').attr('content');
            var table = $('#report-table').DataTable({
                processing: true,
                serverSide: true,
                dom: 'Blfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All'],
                ],
                ajax: {
                    type: "POST",
                    url: "{{ route('report.iuran.list') }}",
                    data: {
                        "awal": awal,
                        "akhir": akhir,
                        'payment': payment,
                        'penyelia': penyelia,
                        '_token': csrf_token
                    }
                },
                order: [
                    [0, "desc"]
                ],
                columns: [{
                        data: 'id',
                        name: 'id',
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'paid_at',
                        name: 'paid_at'
                    },
                    {
                        data: 'invoice',
                        name: 'invoice'
                    },
                    {
                        data: 'user_id',
                        name: 'user_id'
                    },
                    {
                        data: 'payment_name',
                        name: 'payment_name'
                    },
                    {
                        data: 'periode',
                        name: 'periode'
                    },
                    {
                        data: 'due_date',
                        name: 'due_date'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'payment_method',
                        name: 'payment_method'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },


                ]
            });
        }

        $("#btn-filter").click(function() {
            var awal = $("#awal").val();
            var akhir = $("#akhir").val();
            var payment = $("#payment").val();
            var penyelia = $("#penyelia").val();

            if (awal == '' || akhir == '') {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal Awal atau Tanggal Akhir Tidak Boleh Kosong...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else if (awal > akhir) {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal tidak valid...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else {

                init_data_table(awal, akhir, payment, penyelia);
            }
        });

        $("#btn-print-financing").click(function() {
            var awal = $("#awal").val();
            var akhir = $("#akhir").val();
            var payment = $("#payment").val();
            var penyelia = $("#penyelia").val();


            if (payment == '') {
                payment = '0';
            }
            if (penyelia == '') {
                penyelia = '0';
            }

            if (awal == '' || akhir == '') {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal Awal atau Tanggal Akhir Tidak Boleh Kosong...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else {

                window.open("{{ url('backdata/print_iuran_financing') }}" + "/" + awal + "/" + akhir + "/" +
                    payment + "/" + penyelia, "_blank");
            }
        })



        $("#btn-export-accounting").click(function() {
            var awal = $("#awal").val();
            var akhir = $("#akhir").val();
            var payment = $("#payment").val();
            var penyelia = $("#penyelia").val();


            if (payment == '') {
                payment = '0';
            }
            if (penyelia == '') {
                penyelia = '0';
            }

            if (awal == '' || akhir == '') {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal Awal atau Tanggal Akhir Tidak Boleh Kosong...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else {
                window.location = "{{ url('backdata/print_export_accounting') }}" + "/" + awal + "/" + akhir +
                    "/" + payment + "/" + penyelia;
                // window.open("{{ url('backdata/print_iuran_financing') }}"+"/"+awal+"/"+akhir+"/"+payment+"/"+penyelia, "_blank");
            }
        })



        $("#btn-print-kas").click(function() {
            var awal = $("#awal").val();
            var akhir = $("#akhir").val();
            var payment = $("#payment").val();
            var penyelia = $("#penyelia").val();


            if (payment == '') {
                payment = '0';
            }
            if (penyelia == '') {
                penyelia = '0';
            }

            if (awal == '' || akhir == '') {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal Awal atau Tanggal Akhir Tidak Boleh Kosong...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else if (awal > akhir) {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal tidak valid...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else {

                window.open("{{ url('backdata/print_kas_detail') }}" + "/" + awal + "/" + akhir + "/" + payment +
                    "/" + penyelia, "_blank");
            }
        })


        function printData(id) {
            window.open('{{ url('backdata/kwitansi') }}' + '/' + id, '_blank');
        }
    </script>
@endif

@if ($view == 'report-unit')
    <script>
        init_data_table("", "", "", "");

        function init_data_table(awal, akhir, payment, unit) {
            $("#report-table").dataTable().fnDestroy();

            var csrf_token = $('meta[name="csrf-token"]').attr('content');
            var table = $('#report-table').DataTable({
                processing: true,
                serverSide: true,
                dom: 'Blfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All'],
                ],
                ajax: {
                    type: "POST",
                    url: "{{ route('report.unit.list') }}",
                    data: {
                        "awal": awal,
                        "akhir": akhir,
                        "payment": payment,
                        "unit": unit,
                        '_token': csrf_token
                    }
                },
                order: [
                    [0, "desc"]
                ],
                columns: [{
                        data: 'id',
                        name: 'id',
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'paid_at',
                        name: 'paid_at'
                    },
                    {
                        data: 'invoice',
                        name: 'invoice'
                    },
                    {
                        data: 'user_id',
                        name: 'user_id'
                    },
                    {
                        data: 'name_unit',
                        name: 'name_unit'
                    },
                    {
                        data: 'booking_date',
                        name: 'booking_date'
                    },
                    {
                        data: 'booking_time',
                        name: 'booking_time'
                    },
                    {
                        data: 'total_price',
                        name: 'total_price'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'payment_method',
                        name: 'payment_method'
                    },

                    {
                        data: 'created_at',
                        name: 'created_at'
                    },


                ]
            });
        }

        $("#btn-filter").click(function() {
            var awal = $("#awal").val();
            var akhir = $("#akhir").val();
            var payment = $("#payment").val();
            var unit = $("#bisnis_unit").val();

            if (awal == '' || akhir == '') {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal Awal atau Tanggal Akhir Tidak Boleh Kosong...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else if (awal > akhir) {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal tidak valid...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else {

                init_data_table(awal, akhir, payment, unit);
            }
        });


        $("#btn-print-kas").click(function() {
            var awal = $("#awal").val();
            var akhir = $("#akhir").val();
            var payment = $("#payment").val();
            var unit = $("#bisnis_unit").val();

            if (payment == '') {
                payment = '0';
            }

            if (unit == '') {
                unit = '0';
            }

            if (awal == '' || akhir == '') {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal Awal atau Tanggal Akhir Tidak Boleh Kosong...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else if (awal > akhir) {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal tidak valid...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else {

                window.open("{{ url('backdata/print_unit_report') }}" + "/" + awal + "/" + akhir + "/" + payment +
                    "/" + unit, "_blank");
            }
        })


        function printData(id) {
            window.open('{{ url('backdata/kwitansi') }}' + '/' + id, '_blank');
        }


        $("#btn-export-accounting").click(function() {
            var awal = $("#awal").val();
            var akhir = $("#akhir").val();
            var payment = $("#payment").val();
            var unit = $("#bisnis_unit").val();



            if (payment == '') {
                payment = '0';
            }

            if (unit == '') {
                unit = '0';
            }

            if (awal == '' || akhir == '') {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal Awal atau Tanggal Akhir Tidak Boleh Kosong...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else {
                window.location = "{{ url('backdata/print_unit_accounting') }}" + "/" + awal + "/" + akhir + "/" +
                    payment + "/" + unit;
                // window.open("{{ url('backdata/print_iuran_financing') }}"+"/"+awal+"/"+akhir+"/"+payment+"/"+penyelia, "_blank");
            }
        })
    </script>
@endif

@if ($view == 'report-lain')
    <script>
        init_data_table("", "", "", "");

        function init_data_table(awal, akhir, payment, penyelia) {
            $("#report-table").dataTable().fnDestroy();

            var csrf_token = $('meta[name="csrf-token"]').attr('content');
            var table = $('#report-table').DataTable({
                processing: true,
                serverSide: true,
                dom: 'Blfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All'],
                ],
                ajax: {
                    type: "POST",
                    url: "{{ route('report.lain.list') }}",
                    data: {
                        "awal": awal,
                        "akhir": akhir,
                        "payment": payment,
                        "penyelia": penyelia,
                        '_token': csrf_token
                    }
                },
                order: [
                    [0, "desc"]
                ],
                columns: [{
                        data: 'id',
                        name: 'id',
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'paid_at',
                        name: 'paid_at'
                    },
                    {
                        data: 'invoice',
                        name: 'invoice'
                    },
                    {
                        data: 'user_id',
                        name: 'user_id'
                    },
                    {
                        data: 'payment_name',
                        name: 'payment_name'
                    },
                    {
                        data: 'periode',
                        name: 'periode'
                    },
                    {
                        data: 'due_date',
                        name: 'due_date'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'payment_method',
                        name: 'payment_method'
                    },

                    {
                        data: 'created_at',
                        name: 'created_at'
                    },


                ]
            });
        }

        $("#btn-filter").click(function() {
            var awal = $("#awal").val();
            var akhir = $("#akhir").val();
            var payment = $("#payment").val();
            var penyelia = $("#penyelia").val();

            if (awal == '' || akhir == '') {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal Awal atau Tanggal Akhir Tidak Boleh Kosong...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else if (awal > akhir) {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal tidak valid...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else {

                init_data_table(awal, akhir, payment, penyelia);
            }
        });



        $("#btn-print-kas").click(function() {
            var awal = $("#awal").val();
            var akhir = $("#akhir").val();
            var payment = $("#payment").val();
            var penyelia = $("#penyelia").val();

            if (payment == '') {
                payment = 0;
            }

            if (penyelia == '') {
                penyelia = 0;
            }

            if (awal == '' || akhir == '') {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal Awal atau Tanggal Akhir Tidak Boleh Kosong...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else if (awal > akhir) {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal tidak valid...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else {
                console.log(penyelia);
                window.open("{{ url('backdata/print_lain_report') }}" + "/" + awal + "/" + akhir + "/" + payment +
                    "/" + penyelia, "_blank");
            }


        })


        function printData(id) {
            window.open('{{ url('backdata/kwitansi') }}' + '/' + id, '_blank');
        }

        $("#btn-export-accounting").click(function() {
            var awal = $("#awal").val();
            var akhir = $("#akhir").val();
            var payment = $("#payment").val();
            var penyelia = $("#penyelia").val();


            if (payment == '') {
                payment = '0';
            }
            if (penyelia == '') {
                penyelia = '0';
            }

            if (awal == '' || akhir == '') {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal Awal atau Tanggal Akhir Tidak Boleh Kosong...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else {
                window.location = "{{ url('backdata/print_lain_accounting') }}" + "/" + awal + "/" + akhir + "/" +
                    payment + "/" + penyelia;
                // window.open("{{ url('backdata/print_iuran_financing') }}"+"/"+awal+"/"+akhir+"/"+payment+"/"+penyelia, "_blank");
            }
        })
    </script>
@endif


@if ($view == 'report-activity')
    <script>
        init_data_table("", "", "", "");

        function init_data_table(awal, akhir, admin_id, activity_type) {
            $("#report-table").dataTable().fnDestroy();

            var csrf_token = $('meta[name="csrf-token"]').attr('content');
            var table = $('#report-table').DataTable({
                processing: true,
                serverSide: true,
                dom: 'Blfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All'],
                ],
                ajax: {
                    type: "POST",
                    url: "{{ route('report.activity.list') }}",
                    data: {
                        "awal": awal,
                        "akhir": akhir,
                        "admin_id": admin_id,
                        "activity_type": activity_type,
                        '_token': csrf_token
                    }
                },
                order: [
                    [0, "desc"]
                ],
                columns: [{
                        data: 'id',
                        name: 'id',
                        searchable: false
                    },

                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'user_id',
                        name: 'user_id'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },

                ]
            });
        }

        $("#btn-filter").click(function() {
            var awal = $("#awal").val();
            var akhir = $("#akhir").val();
            var user_id = $("#user_id").val();
            var activity = $("#activity").val();

            if (awal == '' || akhir == '') {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal Awal atau Tanggal Akhir Tidak Boleh Kosong...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else if (awal > akhir) {
                Swal.fire({
                    icon: 'error',
                    title: "Tanggal tidak valid...!",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else {

                init_data_table(awal, akhir, user_id, activity);
            }
        });
    </script>
@endif

@if ($view == 'setting-list')
    <script>
        $(document).ready(function() {
            CKEDITOR.replace('term');
            CKEDITOR.replace('privacy');
        })

        $("#btn-upgrade-iuran").click(function() {
            var up = $("#upgrade-iuran").val();
            var blok = $("#blok").val();
            Swal.fire({
                icon: 'question',
                title: 'Upgrade iuranan bulanan user..?',

                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Upgrade',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    var csrf_token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: "{{ url('backdata/upgrade_iuran_bulanan') }}",
                        type: "POST",
                        data: {
                            'up': up,
                            'blok': blok,
                            '_token': csrf_token
                        },
                        success: function(data) {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: data.message,
                                    showConfirmButton: false,
                                    scrollbarPadding: false,
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: data.message,
                                    showConfirmButton: false,
                                    scrollbarPadding: false,
                                });
                            }
                        }
                    });
                }
            });
        });

        var table = $('#table-blok-setting').DataTable({
            processing: true,
            serverSide: true,

            ajax: "{{ route('blok.list') }}",
            order: [
                [0, "desc"]
            ],
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'blok_name',
                    name: 'blok_name'
                },
            ]
        });

        function editData(id) {
            $.ajax({
                url: "{{ url('backdata/blok_edit') }}" + "/" + id,
                type: "GET",
                dataType: "JSON",
                success: function(data) {
                    $("#_method").val("edit");
                    $("#blok_id").val(data.id);
                    $("#blok-tambah").val(data.blok_name);
                }
            })
        }


        function deleteData(id) {
            Swal.fire({
                icon: 'question',
                title: 'Delete this data?',

                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    var csrf_token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: "{{ url('backdata/blok_delete') }}",
                        type: "POST",
                        data: {
                            'id': id,
                            '_token': csrf_token
                        },
                        success: function($data) {
                            table.ajax.reload(null, false);
                        }
                    });
                }
            });
        }


        $("#btn-add-blok").click(function() {
            var id = $("#blok_id").val();
            var blok = $("#blok-tambah").val();
            var method = $("#_method").val();
            var csrf_token = $('meta[name="csrf-token"]').attr('content');
            if (blok == '') {
                Swal.fire({
                    icon: 'error',
                    title: "Blok can not be empty",
                    showConfirmButton: false,
                    scrollbarPadding: false,
                });
            } else {
                $.ajax({
                    url: "{{ url('backdata/add_blok') }}",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        'id': id,
                        'blok': blok,
                        'method': method,
                        '_token': csrf_token
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.success) {
                            table.ajax.reload(null, false);
                            $("#blok-tambah").val("");
                        }
                    }
                })
            }

        });
    </script>
@endif

@if ($view == 'booking-setting')
    <script>
        $("#type").change(function() {
            var tipe = $(this).val();
            if (tipe == 1) {
                $("#day-container").hide();
                $("#date-container").show();
            } else if (tipe == 2) {
                $("#day-container").show();
                $("#date-container").hide();
            }
        });

        var table = $('#listTable').DataTable({
            processing: true,
            serverSide: true,
            dom: 'Blfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All'],
            ],
            ajax: "{{ route('booking.setting.list') }}",
            order: [
                [0, "desc"]
            ],
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'unit_id',
                    name: 'unit_id'
                },
                {
                    data: 'booking_day',
                    name: 'booking_day'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'booking_time',
                    name: 'booking_time'
                },
                {
                    data: 'is_active',
                    name: 'is_active'
                },

            ]
        });

        function addData() {
            resetForm();

            save_method = "add";
            $('input[name=_method]').val('POST');
            $(".modal-title").text("Add Booking Setting");
            $("#modal-tambah").modal("show");
        }

        $("#form-tambah").submit(function(e) {
            loading("btn-save-data");
            e.preventDefault();
            var id = $('#id').val();
            if (save_method == "add") url = "{{ url('/backdata/booking_setting') }}";
            else url = "{{ url('/backdata/booking_setting') . '/' }}" + id;
            $.ajax({
                url: url,
                type: "POST",
                data: new FormData($('#modal-tambah form')[0]),
                contentType: false,
                processData: false,
                success: function(data) {
                    unloading("btn-save-data", "Save");
                    if (data.success) {
                        $('#modal-tambah').modal('hide');
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: data.message,
                            showConfirmButton: false,
                            scrollbarPadding: false,
                        });
                    }
                }

            });
        });

        function editData(id) {
            save_method = "edit";
            $('input[name=_method]').val('PATCH');
            $.ajax({
                url: "{{ url('/backdata/booking_setting') }}" + "/" + id + "/edit",
                type: "GET",
                dataType: "JSON",
                success: function(data) {
                    $('#modal-tambah').modal("show");
                    $('.modal-title').text("Edit Data");
                    $('#id').val(data.id);
                    $("#type").val(data.type);
                    $("#unit_id").val(data.unit_id);
                    $("#date").val(data.date);
                    $("#booking_day").val(data.booking_day);
                    $("#start_time").val(data.start_time);
                    $("#finish_time").val(data.finish_time);
                    $("#is_active").val(data.is_active);

                    if (data.type == 1) {
                        $("#date-container").show();
                        $("#day-container").hide();
                    } else if (data.type == 2) {
                        $("#date-container").hide();
                        $("#day-container").show();
                    }

                }
            })
        }

        function deleteData(id) {
            Swal.fire({
                icon: 'question',
                title: 'Delete this data?',

                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    var csrf_token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: "{{ url('/backdata/booking_setting') }}" + '/' + id,
                        type: "POST",
                        data: {
                            '_method': 'DELETE',
                            '_token': csrf_token
                        },
                        success: function($data) {
                            table.ajax.reload(null, false);
                        }
                    });
                }
            });
        }

        function detailData(id) {
            $.ajax({
                url: "{{ url('backdata/user') }}" + "/" + id,
                type: "GET",
                success: function(data) {
                    $("#detail-content").html(data);
                    $("#modal-detail").modal("show");
                }
            });
        }

        $("#btn-print-detail").click(function() {
            var id = $("#id-detail").val();
            window.open('{{ url('backdata/print_detail') }}' + '/' + id, '_blank');

        });

        function resetForm() {
            $("#name").val("");
            $("#username").val("");
            $("#email").val("");
            $("#password").val("");
            $("#jenis_kelamin").val("");
            $("#no_hp").val("");
            $("#level").val("");
            $("#is_active").val("");
            $("#penyelia").val("");
            $("#blok").val("");
            $("#nomor_rumah").val("");
            $("#daya_listrik").val("");
            $("#luas_tanah").val("");
            $("#iuran_bulanan").val("");
            $("#whatsapp_emergency").val("");
            $("#keterangan").val("");
            $("#alamat_surat_menyurat").val("");
            $("#nomor_telepon_rumah").val("");
            $("#id_pelanggan_pdam").val("");
            $("#nomor_meter_pln").val("");
            $("#mulai_menempati").val("");
            $("#image").val(null);
            $("#profile-image").attr('src', '{{ asset('template/images/profil_icon.png') }}');
            $("#remove-profile-image").hide();
        }
    </script>
@endif

@if ($view == 'outstanding')
    <script>


       


        var table = $('#listTable').DataTable({
            processing: true,
            serverSide: true,
            dom: 'Blfrtip',
            buttons: [
                'csv', 'excel', 'pdf'
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All'],
            ],
            ajax: "{{ route('outstanding.list') }}",
            order: [
                [0, "asc"]
            ],
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'blok',
                    name: 'blok'
                },
                {
                    data: 'nomor_rumah',
                    name: 'nomor_rumah'
                },
                {
                    data: 'penyelia',
                    name: 'penyelia'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'denda',
                    name: 'denda'
                },
                {
                    data: 'iuran',
                    name: 'iuran'
                },
                {
                    data: 'adjustment',
                    name: 'adjustment'
                },
                {
                    data: 'total_outstanding',
                    name: 'total_outstanding'
                },
                {
                    data: 'next_bill',
                    name: 'next_bill'
                },
                {
                    data: 'last_paid',
                    name: 'last_paid'
                },
            ]
        });

        table.on( 'draw', function () {
            var next_bil = $(".next-bill").length;
            console.log(next_bil);
        });

        function add_adjustment(id) {
            $("#modal-adjustment").modal("show");
            $("#userid").val(id);
        }

        function detailData(id) {
            $.ajax({
                url: "{{ url('backdata/outstanding') }}" + "/" + id,
                type: "GET",
                success: function(data) {
                    $("#detail-content").html(data);
                    $("#modal-detail").modal("show");
                }
            });
        }

        $("#btn-save-data").click(function() {
            var description = $("#description").val();
            var type = $("#type").val();
            var amount = $("#amount").val();
            var user_id = $("#userid").val();

            Swal.fire({
                icon: 'question',
                title: 'Save this adjustment? You can not delete or edit this data after your save it',

                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Save',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    var csrf_token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: "{{ url('/backdata/save_adjustment') }}",
                        type: "POST",
                        data: {
                            'description': description,
                            'type': type,
                            'amount': amount,
                            'user_id': user_id,
                            '_token': csrf_token
                        },
                        success: function(data) {

                            if (data.success) {
                                $("#modal-adjustment").modal("hide");
                                detailData(user_id);
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: data.message,
                                    showConfirmButton: false,
                                    scrollbarPadding: false,
                                });

                            }
                        }
                    });
                }
            });
        })
    </script>
@endif

@if ($view == 'dashboard')
    <script>
        var table = $('#table-outstanding-payment').DataTable({
            processing: true,
            serverSide: true,
            dom: 'Blfrtip',
            buttons: [
                'csv', 'excel', 'pdf'
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All'],
            ],
            ajax: "{{ route('outstanding.dashboard') }}",
            order: [
                [0, "asc"]
            ],
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'blok',
                    name: 'blok'
                },
                {
                    data: 'nomor_rumah',
                    name: 'nomor_rumah'
                },
                {
                    data: 'penyelia',
                    name: 'penyelia'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'denda',
                    name: 'denda'
                },
                {
                    data: 'iuran',
                    name: 'iuran'
                },
                {
                    data: 'adjustment',
                    name: 'adjustment'
                },
                {
                    data: 'total_outstanding',
                    name: 'total_outstanding'
                },
                {
                    data: 'last_paid',
                    name: 'last_paid'
                },
            ]
        });
    </script>
@endif