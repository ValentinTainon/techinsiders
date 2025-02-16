import { SimpleUploadConfig } from "./types/SimpleUploadConfig";

export class SimpleUploadProperty {
  static getConfig(uploadDir: string): SimpleUploadConfig {
    return {
      simpleUpload: {
        headers: {
          "X-CSRF-TOKEN": "CSRF-Token",
          Authorization: "Bearer <JSON Web Token>",
          "Upload-Directory": uploadDir,
        },
        uploadUrl: "/ckeditor5-simple-upload",
        withCredentials: true,
      },
    };
  }
}
