@extends('Admin.layouts.app')
@section('title') Blog @endsection
@section('page-title') <h3 class="mb-0 page_title"><a href="{{route('admin.get-blog-list')}}">Blog Management</a> > Edit Blog</h3> @endsection
@section('content')
<style>
    input[type="file"] {
        display: block;
    }
</style>
<div class="container-fluid">
    <div class="profile_rapper d-block shadow">
        <div class="row">
            <div class="col-lg-12 mb-3 mb-lg-4">
                <div class="position-relative">
                    <label class="login_label" for="">Blog Title</label>
                    <input class="login_input paddingStartInput w-100" id="blogTitle" value="{{$blog->title}}" type="text" placeholder="Enter" required>
                </div>
            </div>
            <span class="text-danger validations" id="title-error"></span>
            <div class="col-lg-12 editor">
                <textarea id="summerNote">{{$blog->content}}</textarea> 
            </div>
            <span class="text-danger validations" id="content-error"></span>

            <div class=" col-lg-12 mb-3">
                <h4 class="marginTopInput mb-0">Upload Images</h4>
            </div>
            <div class="col-lg-12 ">
                <div class="upload-field" onclick="document.getElementById('files').click()">
                    <i>&#128247;</i> 
                    <span>Accepted formats: JPEG, PNG, JPG</span>
                </div>
                <div class="multiple_upload">
                    <input type="file" id="files" name="files[]" multiple accept="image/jpeg,image/png,image/jpg" />
                    @if($blog->blogFiles->count() > 0)
                        @foreach($blog->blogFiles as $file)
                            <span class="pip">
                                <img class="imageThumb" src="{{$file->file}}">
                                <span class="remove" data-existing="true" data-id="{{$file->id}}">X</span>
                            </span>
                        @endforeach
                    @endif
                </div>
                <input type="hidden" id="removedIds" name="removedIds" value="[]">
            </div>

            <span class="text-danger validations" id="blog_files-error"></span>
            <div class="col-lg-12 text-end mt-4">
                <button id="updateBlogBtn" class="login_btn shadow-none maxWidth189 mb-2 w-100">
                    Update Blog
                </button>
            </div>
        </div>
    </div>
</div>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

<script>
    $(document).ready(function () {
        $('#summerNote').summernote({
            height: 300,  
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough']],
                ['para', ['ul', 'ol', 'paragraph']],
                // ['insert', ['link', 'picture','video']],
                ['view', ['fullscreen', 'undo', 'redo']],
                ['uploadcare', ['uploadcare']],
            ],
            image: true,
            callbacks: {
                onImageUpload: function (files) {
                    var data = new FormData();
                    data.append('upload_file', files[0]);
                    $.ajax({
                        url: "{{route('admin.upload-file')}}", 
                        method: 'POST',
                        data: data,
                        contentType: false,
                        processData: false,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            $('#summerNote').summernote('insertImage',response.data.url );
                        },
                        error: function (error) {
                            console.error('Error uploading image:', error);
                        }
                    });
                }
            }
        });
        
    });
</script>
<script>
    $(document).ready(function () {
        const maxFiles = 10;
        const maxSize = 4 * 1024 * 1024; 
        const allowedTypes = ["image/jpeg", "image/png", "image/jpg"];
        let fileCount = $(".pip").length; 
        const removedIds = []; 
        let uploadedFiles = [];
        if (window.File && window.FileList && window.FileReader) {
            $("#files").on("change", function (e) {
                const files = e.target.files;
                const filesLength = files.length;
                if (fileCount + filesLength > maxFiles) {
                    toastr.error(`You can only upload up to ${maxFiles} images in total.`);
                    return;
                }
                for (let i = 0; i < filesLength; i++) {
                    const f = files[i];
                    if (!allowedTypes.includes(f.type)) {
                        toastr.error("Invalid file type. Please upload JPG, JPEG, or PNG files only.");
                        continue;
                    }
                    if (f.size > maxSize) {
                        toastr.error("File size exceeds 4MB. Please upload a smaller file.");
                        continue;
                    }
                    uploadedFiles.push(f);
                    const fileReader = new FileReader();
                    fileReader.onload = (function (e) {
                        const file = e.target;
                        $(`
                            <span class="pip">
                                <img class="imageThumb" src="${e.target.result}" title="${file.name}">
                                <span class="remove">X</span>
                            </span>
                        `).insertAfter("#files");

                        fileCount++;
                        $(".remove").off("click").on("click", function () {
                            const isExisting = $(this).data("existing");
                            const id = $(this).data("id");
                            if (isExisting) {
                                removedIds.push(id); 
                                $("#removedIds").val((JSON.stringifyremovedIds));
                                fileCount--; 
                            }
                            $(this).parent(".pip").remove();
                            uploadedFiles = uploadedFiles.filter(f => f.name !== fileName);
                            if (!isExisting) {
                                fileCount--; 
                            }
                        });
                    });
                    fileReader.readAsDataURL(f);
                }
            });

            $(".remove").off("click").on("click", function () {
                const isExisting = $(this).data("existing");
                const id = $(this).data("id");
                if (isExisting) {
                    removedIds.push(id); 
                    $("#removedIds").val(removedIds); 
                }
                $(this).parent(".pip").remove();
                if (!isExisting) {
                    fileCount--;
                }
            });
        } else {
            toastr.error("Your browser doesn't support the File API.");
        }

        $("#updateBlogBtn").click(function(e) {
            e.preventDefault();
            $(".validations").html(''); 
            const blogId = "{{$blog->id}}";
            const removedImagesIds = $("#removedIds").val();
            const removedImages = removedImagesIds.split(',').map(Number);
            
            const blogTitle = $("#blogTitle").val();
            const blogContent = $("#summerNote").val();
            const files = $("#files")[0].files; 

            if (!blogTitle || !blogContent) {
                toastr.warning("Title and content are required.");
                return;
            }

            let formData = new FormData();
            formData.append("blog_id", blogId);
            const validRemovedImages = removedImages.filter(imageId => !isNaN(imageId));

            if (validRemovedImages.length > 0) {  
                validRemovedImages.forEach((imageId, index) => {
                    formData.append(`removedImages[${index}]`, imageId);
                });
            }
            formData.append("title", blogTitle);
            formData.append("content", blogContent);                

            if (uploadedFiles.length > 0) {
                uploadedFiles.forEach(file => formData.append("blog_files[]", file));
            }
            $.ajax({
                url: "{{ route('admin.add-blog-post') }}", 
                type: 'POST',
                data: formData,
                processData: false, 
                contentType: false, 
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    window.location.href = response.data.redirect_url;     
                },
                error: function(xhr, status, error) {
                    if (xhr.status == 422) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessages = '';
                        $.each(errors, function(key, value) {
                            errorMessages = value[0];
                            $('#' + key + '-error').html(errorMessages);
                        });
                        toastr.warning("Validation error");
                    } else {
                        toastr.error(xhr.responseJSON.message);
                    }
                }
            });
        });
    });
</script>




@endsection