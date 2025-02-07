export interface SimpleUploadConfig {
  simpleUpload: {
    headers: Record<string, string | undefined> | undefined;
    uploadUrl: string;
    withCredentials: boolean | undefined;
  };
}
