@extends('Admin.layouts.app')
@section('title') Category @endsection
@section('page-title') <h3 class="mb-0 page_title"><a href="{{route('admin.get-all-category')}}">Category Management</a> > Edit Category</h3> @endsection
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
                    <label class="login_label" for="">Category Name</label>
                    <input class="login_input paddingStartInput w-100" id="categoryName" type="text" placeholder="Enter" value="{{$category->name}}" required>
                </div>
            </div>
            <span class="text-danger validations" id="name-error"></span>
            <div class="col-lg-12 mb-3 mb-lg-4">
                <div class="position-relative">
                    <label class="login_label" for="">Category Description</label>
                    <input class="login_input paddingStartInput w-100" id="description" value="{{$category->description}}" type="text" placeholder="Enter Description" required>
                </div>
            </div>
            <span class="text-danger validations" id="description-error"></span>
            <div class=" col-lg-12 mb-3">
                <h4 class="marginTopInput mb-0">Upload Image</h4>
            </div>
            <div class="col-lg-12 ">
                <div class="upload-field" onclick="document.getElementById('files').click()">
                    <i>&#128247;</i> 
                    <span>Accepted formats: JPEG, PNG, JPG</span>
                </div>
                <div class="multiple_upload">
                    <input type="file" id="files" name="files[]" multiple accept="image/jpeg,image/png,image/jpg" />
                    <span class="pip">
                        <img class="imageThumb" src="{{asset($category->image)}}" > 
                    </span>
                </div>
            </div>

            <span class="text-danger validations" id="category_image-error"></span>
            <div class="col-lg-12 text-end mt-4">
                <button id="saveCategoryBtn" class="login_btn shadow-none maxWidth189 mb-2 w-100">
                    Edit Category
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const maxSize = 4 * 1024 * 1024; 
        const allowedTypes = ["image/jpeg", "image/png", "image/jpg"];

        if (window.File && window.FileList && window.FileReader) {
            $("#files").on("change", function (e) {
                const files = Array.from(e.target.files);

                if (files.length !== 1) {
                    toastr.error("You can only upload one file.");
                    $(this).val(""); 
                    return;
                }

                const file = files[0];

                if (!allowedTypes.includes(file.type)) {
                    toastr.error("Invalid file type. Please upload JPG, JPEG, or PNG files only.");
                    $(this).val(""); 
                    return;
                }
                if (file.size > maxSize) {
                    toastr.error("File size exceeds 4MB. Please upload a smaller file.");
                    $(this).val(""); 
                    return;
                }

                $(".pip").remove();

                const fileReader = new FileReader();
                fileReader.onload = function (e) {
                    const fileHtml = `
                        <span class="pip">
                            <img class="imageThumb" src="${e.target.result}" title="${file.name}">
                        </span>
                    `;
                    $(fileHtml).insertAfter("#files");
                };
                fileReader.readAsDataURL(file);
            });

            $(document).on("click", ".remove", function () {
                $(".pip").remove();
                $("#files").val("");
            });
        } else {
            toastr.error("Your browser doesn't support the File API.");
        }

        $("#saveCategoryBtn").click(function (e) {
            e.preventDefault();
            $(".validations").html("");

            const categoryName = $("#categoryName").val();
            const description = $("#description").val();
            const files = $("#files")[0].files;

            if (!categoryName) {
                toastr.warning("Title is required.");
                return;
            }

            // if (files.length === 0) {
            //     toastr.warning("Please upload an image.");
            //     return;
            // }

            let formData = new FormData();
            formData.append("name", categoryName);
            formData.append("description", description);
            formData.append("category_id", "{{$category->id}}");
            formData.append("category_image", files[0]);

            $.ajax({
                url: "{{ route('admin.add-category-post') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                },
                success: function (response) {
                    if (response.data && response.data.redirect_url) {
                        window.location.href = response.data.redirect_url;
                    } else {
                        toastr.success("Category added successfully.");
                    }
                },
                error: function (xhr) {
                    if (xhr.status == 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function (key, value) {
                            $("#" + key + "-error").html(value[0]);
                        });
                        toastr.warning("Validation error");
                    } else {
                        toastr.error(xhr.responseJSON.message || "Something went wrong.");
                    }
                },
            });
        });
    });
</script>
@endsection