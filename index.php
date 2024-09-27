<?php
require_once __DIR__ . "/Config.php";
require_once __DIR__ . "/DataSource.php";

$ds = new DataSource();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = ['success' => false, 'message' => '', 'data' => null];

    if ($_POST['action'] === 'shorten') {
        $originalURL = filter_input(INPUT_POST, 'original_url', FILTER_SANITIZE_URL);
        if (filter_var($originalURL, FILTER_VALIDATE_URL)) {
            // Check if the URL already exists
            $checkQuery = "SELECT short_url_hash FROM tbl_url WHERE original_url = ?";
            $checkParamType = "s";
            $checkParamValueArray = array($originalURL);
            $existingURL = $ds->select($checkQuery, $checkParamType, $checkParamValueArray);

            if ($existingURL) {
                // URL exists
                $response['success'] = true;
                $response['message'] = "URL already exists. You can access the shortcode from below.";
                $response['data'] = [
                    'original_url' => $originalURL,
                    'short_url' => Config::ROOT_URL . $existingURL[0]['short_url_hash']
                ];
            } else {
                // Generate new short URL
                $shortURL = mt_rand(1000, 9999);
                $query = "INSERT INTO tbl_url (short_url_hash, original_url) VALUES (?, ?)";
                $paramType = "ss";
                $paramValueArray = array($shortURL, $originalURL);
                
                if ($ds->insert($query, $paramType, $paramValueArray)) {
                    $response['success'] = true;
                    $response['data'] = [
                        'original_url' => $originalURL,
                        'short_url' => Config::ROOT_URL . $shortURL
                    ];
                } else {
                    $response['message'] = "Failed to shorten URL.";
                }
            }
        } else {
            $response['message'] = "Invalid URL provided.";
        }
    } elseif ($_POST['action'] === 'delete') {
        $shortURL = filter_input(INPUT_POST, 'short_url', FILTER_SANITIZE_STRING);
        $query = "DELETE FROM tbl_url WHERE short_url_hash = ?";
        $paramType = "s";
        $paramValueArray = array($shortURL);
        
        if ($ds->delete($query, $paramType, $paramValueArray)) {
            $response['success'] = true;
        } else {
            $response['message'] = "Failed to delete URL.";
        }
    } elseif ($_POST['action'] === 'list') {
        $query = "SELECT * FROM tbl_url";
        $result = $ds->select($query);
        if ($result) {
            $response['success'] = true;
            $response['data'] = $result;
        } else {
            $response['message'] = "No URLs found.";
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>URL Shortener</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">URL Shortener</h1>
        <div id="message" class="alert" style="display: none; position: relative;">
            <button type="button" class="btn-close" aria-label="Close" style="position: absolute; right: 1rem; top: 1rem;"></button>
            <span id="message-content"></span>
        </div>
        <form id="frm-url-shortener">
            <div class="mb-3">
                <label for="original_url" class="form-label">Enter long URL</label>
                <input type="text" class="form-control" name="original_url" id="original_url" placeholder="e.g. http://www.example.com/querystring" autocomplete="off" required>
            </div>
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Shorten the URL</button>
            </div>
        </form>
        
        <div id="shortened-url-response" class="shortened-url-response" style="display: none;">
            <p><strong>Original URL:</strong> <span id="original_url_response"></span></p>
            <p><strong>Shortened URL:</strong> <span id="shortened_url_response"></span></p>
        </div>

        <h2 class="mt-5">All Shortened URLs</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Original URL</th>
                    <th>Shortened URL</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="url-list">
                <!-- URL list will be populated here -->
            </tbody>
        </table>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        function showMessage(message, isError = false) {
            $('#message').removeClass('alert-danger alert-success')
                         .addClass(isError ? 'alert-danger' : 'alert-success')
                         .show();
            $('#message-content').html(message);
        }

        function validateLongURL() {
            var originalURL = $('#original_url').val();
            var urlRegexPattern = /^(ftp|http|https):\/\/[^ "]+$/;
            if (originalURL === "") {
                showMessage('URL is required.', true);
                return false;
            } else if (!urlRegexPattern.test(originalURL)) {
                showMessage('Invalid URL', true);
                return false;
            }
            return true;
        }

        function updateURLList() {
            $.post('index.php', {action: 'list'}, function(response) {
                if (response.success) {
                    var html = '';
                    response.data.forEach(function(url) {
                        html += '<tr>' +
                            '<td><a href="' + url.original_url + '" target="_blank">' + url.original_url + '</a></td>' +
                            '<td><a href="' + '<?php echo Config::ROOT_URL; ?>' + url.short_url_hash + '" target="_blank">' + '<?php echo Config::ROOT_URL; ?>' + url.short_url_hash + '</a></td>' +
                            '<td><button class="btn btn-danger btn-sm delete-url" data-short-url="' + url.short_url_hash + '">Delete</button></td>' +
                        '</tr>';
                    });
                    $('#url-list').html(html);
                } else {
                    showMessage(response.message, true);
                }
            });
        }

        $(document).ready(function() {
            updateURLList();

            $('#frm-url-shortener').on('submit', function(e) {
                e.preventDefault();
                if (validateLongURL()) {
                    $.post('index.php', {
                        action: 'shorten',
                        original_url: $('#original_url').val()
                    }, function(response) {
                        if (response.success) {
                            if (response.message) {
                                showMessage(response.message); // Show warning message if URL already exists
                                $('#original_url_response').text(response.data.original_url);
                                $('#shortened_url_response').text(response.data.short_url);
                                $('#shortened-url-response').show();
                            } else {
                                $('#original_url_response').text(response.data.original_url);
                                $('#shortened_url_response').text(response.data.short_url);
                                $('#shortened-url-response').show();
                                showMessage('URL shortened successfully!');
                            }
                            updateURLList();
                        } else {
                            showMessage(response.message, true);
                        }
                    });
                }
            });

            $(document).on('click', '.delete-url', function() {
                var shortURL = $(this).data('short-url');
                if (confirm('Are you sure you want to delete this URL?')) {
                    $.post('index.php', {
                        action: 'delete',
                        short_url: shortURL
                    }, function(response) {
                        if (response.success) {
                            showMessage('URL deleted successfully!');
                            updateURLList();
                        } else {
                            showMessage(response.message, true);
                        }
                    });
                }
            });

            // Close message alert
            $(document).on('click', '#message .btn-close', function() {
                $('#message').hide();
            });
        });
    </script>
</body>
</html>
