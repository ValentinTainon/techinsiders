// @ts-ignore
import { Plugin, SimpleUploadAdapter } from "ckeditor5";

export class SimpleUploadCleaner extends Plugin {
  public static get pluginName() {
    return "SimpleUploadCleaner" as const;
  }

  public static get requires() {
    return [SimpleUploadAdapter];
  }

  public init(): void {
    // @ts-ignore
    const editor = this.editor;
    const simpleUploadCleanerConfig = editor.config.get("simpleUploadCleaner");
    const cleanUrl: string = simpleUploadCleanerConfig.cleanUrl;
    const uploadDir: string = simpleUploadCleanerConfig.uploadDir;

    let isFormSubmitted: boolean = false;
    let initialEditorImages: Array<string | null | undefined> = [];

    editor.once("ready", () => {
      initialEditorImages = this.getEditorImages(editor.getData());
    });

    document.addEventListener("ea.form.submit", () => {
      isFormSubmitted = true;

      this.cleanUnusedImagesOnSubmit(
        cleanUrl,
        this.createRequestPayload(
          uploadDir,
          this.getEditorImages(editor.getData())
        )
      );
    });

    window.addEventListener("beforeunload", () => {
      if (isFormSubmitted) return;

      this.cleanUnusedImagesBeforeUnload(
        cleanUrl,
        this.createRequestPayload(uploadDir, initialEditorImages)
      );
    });
  }

  private getEditorImages(editorData: string): Array<string | undefined> {
    try {
      const domParser = new DOMParser();
      const editorDocument = domParser.parseFromString(editorData, "text/html");
      const editorImages = editorDocument.querySelectorAll("img");
      const editorImagesSrc = Array.from(editorImages).map((img) =>
        img.getAttribute("src")
      );
      const editorImagesFileName = editorImagesSrc.map((src) => {
        if (src !== null) return src.split("/").at(-1);
      });

      return editorImagesFileName;
    } catch (error) {
      throw new Error("Failed to get editor images name: ", error);
    }
  }

  private createRequestPayload(
    uploadDir: string,
    editorImages: Array<string | null | undefined>
  ): string {
    return JSON.stringify({
      uploadDir: uploadDir,
      editorImages: editorImages,
    });
  }

  private cleanUnusedImagesOnSubmit(cleanUrl: string, payload: string): void {
    fetch(cleanUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: payload,
    })
      .then((response) => response.json())
      .then((response) => {
        if (response.status) {
          console.log(response.status);
        } else if (response.error) {
          console.error(response.error);
        }
      })
      .catch((error: Error) => {
        console.error("Clean unused images on submit: ", error);
        alert(error);
      });
  }

  private cleanUnusedImagesBeforeUnload(
    cleanUrl: string,
    payload: string
  ): void {
    try {
      const data = new Blob([payload], {
        type: "application/json",
      });

      const sendBeaconAction = navigator.sendBeacon(cleanUrl, data);

      if (!sendBeaconAction) {
        console.error(
          "SendBeaconAction failed to launch cleaning images process."
        );
      }
    } catch (error) {
      console.error("Clean unused images before unload: ", error);
      alert(error);
    }
  }
}
