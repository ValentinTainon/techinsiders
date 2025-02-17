import { SimpleUploadCleanerConfig } from "./types/SimpleUploadCleanerConfig";

export class SimpleUploadCleanerProperty {
  static getConfig(uploadDir: string): SimpleUploadCleanerConfig {
    return {
      simpleUploadCleaner: {
        cleanUrl: "/ckeditor5-simple-upload-cleaner",
        uploadDir: uploadDir,
      },
    };
  }
}
