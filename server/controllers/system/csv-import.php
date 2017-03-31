<?php

class CSVImportController extends Controller {
    const PATH = '/csv-import';
    const METHOD = 'POST';

    public function validations() {
        return [
            'permission' => 'staff_3',
            'requestData' => []
        ];
    }

    public function handler() {
        $fileUploader = $this->uploadFile(true);

        if(!$fileUploader instanceof FileUploader) {
            throw new Exception(ERRORS::INVALID_FILE);
        }

        $file = fopen($fileUploader->getFullFilePath(),'r');
        $errors = [];

        while(!feof($file)) {
            $userList = fgetcsv($file);

            Controller::setDataRequester(function ($key) use ($userList) {
                switch ($key) {
                    case 'email':
                        return $userList[0];
                    case 'password':
                        return $userList[1];
                    case 'name':
                        return $userList[2];
                }

                return null;
            });

            $signupController = new SignUpController(true);

            try {
                $signupController->validate();
                $signupController->handler();
            } catch (\Exception $exception) {
                $errors[] = $exception->getMessage() . ' in email ' . $userList[0];
            }
        }

        fclose($file);
        
        unlink($fileUploader->getFullFilePath());
        
        Response::respondSuccess($errors);
    }
}