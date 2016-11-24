<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заявка</title>
    <style>
        .foto {
            width: 100px;
            height: 100px;
            display: none;
        }    
        .fotoholder {
            cursor: pointer;
            background-color: gray;
            border-width: 1px;
            border-style: solid;
            min-height: 100px;
            min-width: 100px;
            float: left;
            color: white;
        }    
        .fotoholder label {
            display: block;
            margin: 10px;
            cursor: pointer;
            line-height: 100px;
            height: 100px;
        }    
        #file_input {
            display: none;
        } 
        a.remove {
            display: none;    
            clear: both;
            background-color: transparent;
            color: white;
            text-align: center;
            margin-bottom: 5px;
        }    
        progress {
            display: none;    
        }    
    </style>
    <script src="jquery.min.js"></script>
    <script>
        const MAX_FILE_COUNT = 4; 
       // var objURL;
        function changePhoto(sender) {
            var holderid = sender.parentElement.id;
            //console.log(holderid);
            $('#file_input').attr('data-holder', holderid).click();           
        }    
        function changeFile(input) {
            if (input.files.length == 1) {
                //console.log();
                var imgid = input.dataset.holder.replace('holder', 'foto');
                var objURL = window.URL.createObjectURL(input.files[0]);
                $('#' + imgid).on('load', function() {
                    window.URL.revokeObjectURL(objURL); }).attr('src', objURL).show();
                var holder = $('#' + input.dataset.holder);
                holder.find('label').hide();
                holder.find('a').css('display', 'block');
                uploadPhoto(input.files[0], imgid.replace('foto',''));
            }    
        }
        function removePhoto(src) {
            var holder = $('#' + src.parentElement.id);
            var img = holder.find('img');
            $(img).attr('src', '').hide();
            holder.find('label').css('display', 'block');
            $(src).hide();
        }    
    </script>
    <script>
        const MAX_SIZE = 800;
        
        window.uploadPhoto = function(file, no){
            // Read in file
            //var file = event.target.files[0];

            // Ensure it's an image
            if(file.type.match(/image.*/)) {
                console.log('An image has been loaded');

                // Load the image
                var reader = new FileReader();
                progress = $('#p' + no)[0];
                reader.onprogress = function(event) {
                    if (event.lengthComputable) {
                        progress.max = event.total+2;
                        progress.value = event.loaded;
                    }
                };
                reader.onloadstart = function(event) {
                        progress.max = 1;
                        progress.value = 1;
                        $(progress).css('display', 'block');
                };
                reader.onloadend = function(event) {
                    //var contents = event.target.result,
                    error = event.target.error;
                    if (error != null) {
                        console.error("File could not be read! Code " + error.code);
                        $(progress).hide();
                    } else {
                        //progress.max = 1;
                        //progress.value = 1;
                        
                        //console.log("Contents: " + contents);
                    }
                    //$(progress).hide();
                };
                
                reader.onload = function (readerEvent) {
                    var image = new Image();
                    image.onload = function (imageEvent) {

                        // Resize the image
                        var canvas = //$('canvas')[0]; 
                                  document.createElement('canvas'),
                            max_size = MAX_SIZE,// TODO : pull max size from a site config
                            width = image.width,
                            height = image.height;
                        if (width > height) {
                            if (width > max_size) {
                                height *= max_size / width;
                                width = max_size;
                            }
                        } else {
                            if (height > max_size) {
                                width *= max_size / height;
                                height = max_size;
                            }
                        }
                        canvas.width = width;
                        canvas.height = height;
                        canvas.getContext('2d').drawImage(image, 0, 0, width, height);
                        var dataUrl = canvas.toDataURL('image/jpeg', .6);
                        progress.value = progress.value + 1;
                        var resizedImage = dataURLToBlob(dataUrl);
                        progress.value = progress.value + 1;
                        $(progress).hide();
                    /*    
                        $.event.trigger({
                            type: "imageResized",
                            blob: resizedImage,
                            url: dataUrl
                        });
                    */    
                    }
                    image.src = readerEvent.target.result;
                }
                reader.readAsDataURL(file);
            }
        };    
        /* Utility function to convert a canvas to a BLOB */
        var dataURLToBlob = function(dataURL) {
            var BASE64_MARKER = ';base64,';
            if (dataURL.indexOf(BASE64_MARKER) == -1) {
                var parts = dataURL.split(',');
                var contentType = parts[0].split(':')[1];
                var raw = parts[1];

                return new Blob([raw], {type: contentType});
            }

            var parts = dataURL.split(BASE64_MARKER);
            var contentType = parts[0].split(':')[1];
            var raw = window.atob(parts[1]);
            var rawLength = raw.length;

            var uInt8Array = new Uint8Array(rawLength);

            for (var i = 0; i < rawLength; ++i) {
                uInt8Array[i] = raw.charCodeAt(i);
            }

            return new Blob([uInt8Array], {type: contentType});
        }
        /* End Utility function to convert a canvas to a BLOB      

        Finally, here is my event handler that takes the blob from the custom event, appends the form and then submits it.

        /* Handle image resized events 
        $(document).on("imageResized", function (event) {
            var data = new FormData($("form[id*='uploadImageForm']")[0]);
            if (event.blob && event.url) {
                data.append('image_data', event.blob);

                $.ajax({
                    url: event.url,
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    type: 'POST',
                    success: function(data){
                       ... handle errors...
                    }
                });
            }
        });        
*/        
    </script>
</head>
<body>
    <section id="foto">
        <input id="file_input" type="file" accept=".png,.jpg,.jpeg,.gif" onchange="changeFile(this)" />
        <script>
            for (var i=1; i<=MAX_FILE_COUNT; i++) {
               var s = '<div class="fotoholder" id="holder' + i + '"><label onclick="changePhoto(this)" >Выбрать...</label>' +
                       '<img class="foto" id="foto' + i + '" src="/img/sample1.png" onclick="changePhoto(this)"/>' +
                       '<progress id="p' + i + '"></progress>' +
                       '<a class="remove" onclick="removePhoto(this)">убрать</a>'; 
               $('#foto').append(s);   
            }    
        </script>
    </section>
    <section><canvas></canvas>
    </section>
</body>
</html>    
    