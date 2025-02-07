import { SimpleUploadConfig } from "./types/SimpleUploadConfig";

export class SimpleUploadProperty {
  static getConfig(postUuid: string | undefined): SimpleUploadConfig {
    return {
      simpleUpload: {
        headers: {
          "X-CSRF-TOKEN": "CSRF-Token",
          Authorization: "Bearer <JSON Web Token>",
          "Post-Uuid": postUuid,
        },
        uploadUrl: "/upload-post-image",
        withCredentials: true,
      },
    };
  }
}
