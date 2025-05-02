<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hall Ticket</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: url('index_bg.jpg') no-repeat center center/cover;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.05);
            z-index: -1;
        }

        header {
            padding: 1.5rem 4rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0, 0, 0, 0.5);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .portal-name {
            color: #fff;
            font-size: 1.8rem;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        header a {
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        header a:hover {
            background-color: rgba(0, 0, 0, 0.4);
        }

        .main-content {
            display: flex;
            flex: 1;
            padding: 2rem;
            overflow: hidden;
        }

        .left-panel {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem; /* Updated spacing between tiles */
            width: 200px;
            height: 102%;
        }

        .menu-item {
            background: rgba(0, 0, 0, 1);
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            transition: transform 0.3s, background-color 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #fff;
        }

        .menu-item:hover {
            background: rgba(0, 0, 0, 0.9);
            transform: translateY(-3px);
        }

        .menu-item i {
            font-size: 2rem;
            margin-bottom: 0.8rem;
        }
        .drop-zone {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .drop-zone.dragover {
            background-color: #e1f5fe;
            border-color: #2196f3;
        }
        .file-input {
            display: none;
        }
        .button {
            background-color: rgb(0, 0, 0);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px;
            transition: background-color 0.3s;
        }
        .idk-number {
            color: #2196f3;
            font-weight: bold;
            margin: 5px 0;
        }
        .button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .button:hover:not(:disabled) {
            background-color: rgb(172, 0, 0);
        }
        .status {
            margin-top: 20px;
            text-align: center;
            color: #666;
            padding: 10px;
        }
        .error {
            color: #f44336;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        #pageList {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .page-container {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
        .page-preview {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }
        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
        .loading::after {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #2196f3;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .dashboard {
            flex: 1;
            margin-left: 2rem;
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 8px;
            overflow-y: auto; /* Make the dashboard scrollable */
            display: flex;
            flex-direction: column;
            gap: 1rem;
            height: 100%; /* Ensure it takes full height of the main content */
        }
        .file-tile {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1rem;
            text-align: left;
            transition: transform 0.3s;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1560px;
            height: 60px;
            margin-bottom: 1rem;
        }
        .dashboard-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }
        .dashboard-header h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .centered {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .logout {
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            margin-left: auto;
            padding-right: 2rem;
        }
        .btn {
            background-color: rgb(0, 0, 0);
            color: white;
            padding: 0.3rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: rgb(172, 0, 0);
        }
        .file-tile h3 {
            font-size: 1.3rem;
            margin: 0;
        }
    </style>
</head>
<body>
<header>
        <div class="portal-name">University Examination Portal</div>
        <div>        
            <a href="un_co.php">Home</a>
            <a href="index.html">Logout</a>
        </div>
    </header>
    <div class="main-content">
        <div class="left-panel">
            <a href="#" class="menu-item">
                <i>🎫</i>
                    Hall Ticket
                </a>
                <a href="un_co_view_report.php" class="menu-item">
                <i>📊</i>
                    View Reports
                </a>
        </div>
        <div class="dashboard">
            <div class="dashboard-header">
                <h2>Hall Ticket</h2>
            </div>
                <div class="drop-zone" id="dropZone">
                    <p>Drag and drop a PDF file here<br>or click to select</p>
                    <input type="file" accept=".pdf" class="file-input" id="fileInput">
                </div>
                <label for="semesterSelect">Select Semester:</label>
                <select id="semesterSelect" class="form-select" aria-label="Select Semester">
                    <option value="" disabled selected>Select your semester</option>
                    <option value="1">Semester 1</option>
                    <option value="2">Semester 2</option>
                    <option value="3">Semester 3</option>
                    <option value="4">Semester 4</option>
                    <option value="5">Semester 5</option>
                    <option value="6">Semester 6</option>
                    <option value="7">Semester 7</option>
                    <option value="8">Semester 8</option>
                </select>
                <button id="postButton" class="button">Post</button>
                <div class="loading" id="loading">Processing PDF...</div>
                <div class="status" id="status"></div>
                <div id="pageList"></div>
        </div>
    </div>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const status = document.getElementById('status');
        const loading = document.getElementById('loading');
        const pageList = document.getElementById('pageList');
        const postButton = document.getElementById('postButton');
        const semesterSelect = document.getElementById('semesterSelect');

        let pdfDocument = null;
        let selectedFile = null;
        let idkNumbers = []; // Array to store IDK numbers for download
        let mainPdfName = ''; // Declare mainPdfName here

        const downloadFileWithAnchor = (filename) => {
            const anchor = document.createElement("a");
            anchor.href = "data:application/pdf;charset=utf-8,";
            anchor.download = `${filename}.pdf`;
            document.body.appendChild(anchor);
            anchor.click();
            document.body.removeChild(anchor);
        };

        async function handleFile(file) {
            if (!file || file.type !== 'application/pdf') {
                showError('Please select a valid PDF file.');
                return;
            }
            
            try {
                loading.style.display = 'block';
                status.textContent = 'Reading PDF file...';
                pageList.innerHTML = '';

                selectedFile = file; // Store the selected file for upload
                mainPdfName = selectedFile.name.replace('.pdf', ''); // Get the main PDF name without extension
                
                // Load the PDF to verify it's valid
                const arrayBuffer = await file.arrayBuffer();
                const pdfDoc = await PDFLib.PDFDocument.load(arrayBuffer);
                
                status.textContent = 'PDF loaded successfully! Click Post to process and upload.';
                loading.style.display = 'none';
            } catch (error) {
                showError('Error reading PDF file: ' + error.message);
                loading.style.display = 'none';
            }
        }

        function showError(message) {
            status.innerHTML = `<div class="error">${message}</div>`;
            loading.style.display = 'none';
        }

        // File Drop Zone Event Listeners
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            } else {
                showError('No file was dropped. Please try again.');
            }
        });

        // File Input Event Listeners
        dropZone.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            } else {
                showError('No file selected. Please try again.');
            }
        });

        postButton.addEventListener('click', async () => {
            if (!selectedFile) {
                showError('Please select a PDF file to upload.');
                return;
            }
            if (!semesterSelect.value) {
                showError('Please select a semester.');
                return;
            }

            try {
                loading.style.display = 'block';
                status.textContent = 'Processing PDF...';
                
                // Get the file as a blob and create a copy
                const fileBlob = selectedFile.slice();
                const fileArrayBuffer = await fileBlob.arrayBuffer();
                
                // Process the PDF first to extract IDK numbers
                const pdfJS = await pdfjsLib.getDocument({ data: fileArrayBuffer }).promise;
                const pageCount = pdfJS.numPages;
                
                // Clear previous IDK numbers
                idkNumbers = [];
                
                // Process each page to extract IDK numbers
                for (let pageNum = 1; pageNum <= pageCount; pageNum++) {
                    const page = await pdfJS.getPage(pageNum);
                    const idkNumber = await extractIDKNumber(page);
                    if (idkNumber) {
                        idkNumbers.push(idkNumber);
                    }
                }

                // Check if we found any IDK numbers
                if (idkNumbers.length === 0) {
                    showError('No registration numbers found in the PDF. Please ensure the PDF contains valid registration numbers.');
                    loading.style.display = 'none';
                    return;
                }

                status.textContent = `Found ${idkNumbers.length} registration numbers. Processing pages...`;
                
                // Create a new blob and buffer for PDF processing
                const processingBlob = selectedFile.slice();
                const processingBuffer = await processingBlob.arrayBuffer();
                const pdfDoc = await PDFLib.PDFDocument.load(processingBuffer);
                
                // Process files in batches of 20
                const BATCH_SIZE = 20;
                let totalSuccess = 0;
                let totalError = 0;

                for (let batchStart = 0; batchStart < idkNumbers.length; batchStart += BATCH_SIZE) {
                    const batchEnd = Math.min(batchStart + BATCH_SIZE, idkNumbers.length);
                    status.textContent = `Processing batch ${Math.floor(batchStart/BATCH_SIZE) + 1} of ${Math.ceil(idkNumbers.length/BATCH_SIZE)}...`;
                    
                    // Create FormData for this batch
                    const splitFormData = new FormData();
                    splitFormData.append('semester', semesterSelect.value);
                    
                    // Process each page in the current batch
                    for (let i = batchStart; i < batchEnd; i++) {
                        try {
                            const newPdf = await PDFLib.PDFDocument.create();
                            const [copiedPage] = await newPdf.copyPages(pdfDoc, [i]);
                            newPdf.addPage(copiedPage);
                            
                            const pdfBytes = await newPdf.save();
                            const blob = new Blob([pdfBytes], { type: 'application/pdf' });
                            
                            // Create file from blob with proper name
                            const fileName = `${idkNumbers[i]}_${mainPdfName}.pdf`;
                            const file = new File([blob], fileName, { type: 'application/pdf' });
                            
                            // Extract branch from IDK number
                            const branch = idkNumbers[i].substring(0, 2); // Assuming branch is the first two characters
                            
                            // Add to FormData for upload
                            splitFormData.append('splits[]', file, fileName);
                            splitFormData.append('reg_no[]', idkNumbers[i]);
                            splitFormData.append('branch[]', branch); // Add branch to FormData
                        } catch (error) {
                            console.error(`Error processing page ${i + 1}:`, error);
                        }
                    }

                    // Upload this batch
                    try {
                        const uploadResponse = await fetch('upload.php', {
                            method: 'POST',
                            body: splitFormData
                        });

                        if (!uploadResponse.ok) {
                            throw new Error(`HTTP error! status: ${uploadResponse.status}`);
                        }

                        const responseText = await uploadResponse.text();
                        console.log('Server response:', responseText);
                        
                        try {
                            const uploadResult = JSON.parse(responseText);
                            if (uploadResult.success) {
                                totalSuccess += (batchEnd - batchStart);
                            } else {
                                totalError += (batchEnd - batchStart);
                                console.error('Batch error:', uploadResult.message);
                            }
                        } catch (parseError) {
                            console.error('Server response:', responseText);
                            totalError += (batchEnd - batchStart);
                        }
                    } catch (error) {
                        console.error('Batch upload error:', error);
                        totalError += (batchEnd - batchStart);
                    }
                }

                // Final status update
                if (totalSuccess > 0) {
                    status.textContent = `Successfully processed ${totalSuccess} pages. Failed: ${totalError}`;
                    // Clear the file input and selection
                    fileInput.value = '';
                    selectedFile = null;
                    semesterSelect.value = '';
                } else {
                    showError(`Failed to process any pages. Total errors: ${totalError}`);
                }
            } catch (error) {
                showError('Error processing PDF: ' + error.message);
            } finally {
                loading.style.display = 'none';
            }
        });

        async function extractIDKNumber(page) {
            try {
                const textContent = await page.getTextContent();
                // Get raw text items for debugging
                console.log('Raw text items:', textContent.items);
                
                // Process text items with more detailed logging
                const textItems = textContent.items.map(item => {
                    console.log('Processing item:', item.str, 'at position:', item.transform);
                    return item.str.trim();
                }).filter(str => str.length > 0);

                // Log individual text items
                console.log('Processed text items:', textItems);

                // Try different text combinations
                const fullText = textItems.join(' ');
                const combinedText = textItems.join('');
                console.log('Full text (with spaces):', fullText);
                console.log('Combined text (no spaces):', combinedText);

                // Look for exact IDK pattern including optional 'l' at the start
                const idkPattern = /L?IDK\d{2}[A-Z]{2}\d{3}/;
                
                // Also try a more flexible pattern that might catch split text
                const flexiblePattern = /L?I\s*D\s*K\s*\d{2}\s*[A-Z]{2}\s*\d{3}/;

                // Check full text with both patterns
                let match = fullText.match(idkPattern) || fullText.match(flexiblePattern);
                if (match) {
                    const found = match[0].replace(/\s+/g, '');
                    console.log('Found IDK number in full text:', found);
                    return found;
                }

                // Check combined text with both patterns
                match = combinedText.match(idkPattern) || combinedText.match(flexiblePattern);
                if (match) {
                    const found = match[0].replace(/\s+/g, '');
                    console.log('Found IDK number in combined text:', found);
                    return found;
                }

                // Try looking for parts of the pattern
                const idkParts = {
                    prefix: /L?I\s*D\s*K/i,
                    numbers: /\d{2}/,
                    letters: /[A-Z]{2}/,
                    suffix: /\d{3}/
                };

                // Check if we can find parts of the pattern
                const hasPrefix = textItems.some(text => idkParts.prefix.test(text));
                const hasNumbers = textItems.some(text => idkParts.numbers.test(text));
                const hasLetters = textItems.some(text => idkParts.letters.test(text));
                const hasSuffix = textItems.some(text => idkParts.suffix.test(text));

                console.log('Pattern parts found:', {
                    prefix: hasPrefix,
                    numbers: hasNumbers,
                    letters: hasLetters,
                    suffix: hasSuffix
                });

                // If we found all parts but no complete match, the text might be split
                if (hasPrefix && hasNumbers && hasLetters && hasSuffix) {
                    console.log('All parts found but split across items');
                }

                console.log('No IDK number found in text items:', textItems);
                return null;
            } catch (error) {
                console.error('Error extracting text:', error);
                return null;
            }
        }
    </script>
</body>
</html>