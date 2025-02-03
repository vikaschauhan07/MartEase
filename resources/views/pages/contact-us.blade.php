<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    <title>Contact Us</title>
    <!-- <link rel="icon" type="image/x-icon" href="{{ asset('Admin/images/favicon.svg') }}"> -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap');
        * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        }

        body {
        font-family: "Rubik", sans-serif;
        }
        .container_rapper {
            width: 100%;
            max-width: 800px;
            padding: 20px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }

        .contact_heading{
            font-size: 28px;
            color: #19191A;
            font-weight: 700;
            text-align: center;
        }       

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            width: auto;
            margin-bottom: 5px;
            padding-left: 12px;
            font-weight: 400;
            font-size: 16px;
        }

        input, textarea {
            width: 100%;
            margin-bottom: 15px;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 16px;
        }

        button {
            width: 100%;
            background-color: #E10E0E;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            padding: 13px 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            max-width: 300px;
            border-radius: 10px;
            margin-top: 15px;
        }

        button:hover {
            background-color: #b61313;
        }

        .info {
            text-align: center;
            margin-top: 20px;
        }

        .info p {
            margin: 5px 0;
            color: #555;
            font-size:14px;
        }
        .star_required{
            color: red;
            position: absolute;
            right: -8px;
            top: -2px;
            font-size: 16px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>
    @include('common-pages.flash-message')
    
   <div class="d-flex align-items-center justify-content-center p-2 p-lg-3">
        <div class="container_rapper">
            <h1 class="contact_heading">Contact Us</h1>
            <form action="{{route('contact-us')}}" method="POST">
                @csrf
                <div class="w-100">
                    <label class="position-relative" for="name">Name
                         <span class="star_required">*</span>
                    </label>
                    
                </div>
                <input type="text" id="name" name="name" value="{{old('name')}}" placeholder="Your Name" required aria-required="true">
                @error('name')
                    <span class="text-danger text-start">{{ $message }}</span>
                @enderror
                <div class="w-100">
                    <label class="position-relative" for="name">Email
                         <span class="star_required">*</span>
                    </label>
                </div>
                <input type="email" id="email" name="email" placeholder="Your Email" value="{{old('email')}}" required aria-required="true">
                @error('email')
                    <span class="text-danger text-start">{{ $message }}</span>
                @enderror
                <div class="w-100">
                    <label for="phone">Phone Number</label>
                </div>
                <input type="tel" id="phone" name="phone" placeholder="Your Phone Number" value="{{old('phone')}}">
                @error('phone')
                    <span class="text-danger text-start">{{ $message }}</span>
                @enderror
                <div class="w-100">
                    <label for="subject">Subject</label>
                </div>
                <input type="text" id="subject" name="subject" placeholder="Subject" value="{{old('subject')}}">
                @error('subject')
                    <span class="text-danger text-start">{{ $message }}</span>
                @enderror
                <div class="w-100">
                    <label class="position-relative" for="name">Message
                         <span class="star_required">*</span>
                    </label>
                </div>
                <textarea id="description" name="description" placeholder="Your Message" rows="5" >{{old('description')}}</textarea>
                @error('description')
                    <span class="text-danger text-start">{{ $message }}</span>
                @enderror
                <button type="submit">Submit</button>
            </form>

            <div class="info">
                <p>Phone: +1 (555) 123-4567</p>
                <p>Email: contact@yourcompany.com</p>
            </div>
        </div>
   </div>
</body>
</html>
