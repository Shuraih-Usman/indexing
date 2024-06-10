# Google Indexing API Script

This project is a PHP script that allows you to submit URLs to the Google Indexing API in batches. It also includes a web interface to input URLs and displays the results. If the quota limit is exceeded, it will show an appropriate error message.

## Features

- Submit multiple URLs for indexing
- Display success or error messages
- Log responses to a JSON file
- Responsive design with a loading overlay

## Prerequisites

- PHP 7.4 or higher
- Composer
- Google Cloud Platform (GCP) project with Indexing API enabled
- Service account with necessary permissions

## Installation

1. **Clone the repository**

    ```bash
    git clone https://github.com/Shuraih-Usman/indexing.git
    cd indexing
    ```

2. **Install dependencies**

    ```bash
    composer install
    ```

3. **Configure your Google service account**

    - Create a service account in your GCP project and download the JSON key file.
    - open the file key and copy the key code, paste them in key.json file in the project.

4. **Set permissions for the logs directory**

    Ensure the web server has write permissions to the `logs` directory.

    ```bash
    mkdir logs
    chmod 0777 logs
    ```

## Usage

1. **Start your local PHP server**

    ```bash
    php -S localhost:8000
    ```

2. **Open your web browser and navigate to**

    ```
    http://localhost:8000
    ```

3. **Submit URLs for indexing**

    - Enter one URL per line in the text area.
    - Click the "Submit" button.
    - The script will display success or error messages and log responses in the `logs` directory.

## Example

# Input

    - https://example.com/page1
    - https://example.com/page2


### Output

```json
{
    "success": true,
    "message": "URLs indexed successfully",
    "responses": [
        {
            "url": "https://example.com/page1",
            "response": {
                // Response details
            }
        },
        {
            "url": "https://example.com/page2",
            "response": {
                // Response details
            }
        }
    ]
} 



