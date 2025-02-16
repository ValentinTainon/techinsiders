// @ts-ignore
import { Plugin } from "ckeditor5";

export class SimpleUploadCleaner extends Plugin {
  public static get pluginName() {
    return "SimpleUploadCleaner" as const;
  }

  public init() {
    // @ts-ignore
    const editor = this.editor;
    const simpleUploadCleanerConfig = editor.config.get("simpleUploadCleaner");
    const pageName: string = simpleUploadCleanerConfig.pageName;
    const cleanUrl: string = simpleUploadCleanerConfig.cleanUrl;
    const uploadDir: string = simpleUploadCleanerConfig.uploadDir;

    let isFormSubmitted = false;
    let initialEditorImages: Array<string | null | undefined> = [];

    if (pageName === "edit") {
      editor.once("ready", () => {
        initialEditorImages = this.getEditorImages(editor.getData());
      });
    }

    document.addEventListener("ea.form.submit", (event: Event) => {
      this.cleanUnusedImagesOnSubmit(
        cleanUrl,
        this.createRequestPayload(
          pageName,
          event.type,
          uploadDir,
          this.getEditorImages(editor.getData())
        )
      );

      isFormSubmitted = true;
    });

    window.addEventListener("beforeunload", (event: BeforeUnloadEvent) => {
      if (isFormSubmitted) return;

      if (pageName === "new") {
        this.cleanUnusedImagesBeforeUnload(
          cleanUrl,
          this.createRequestPayload(pageName, event.type, uploadDir)
        );
      } else if (pageName === "edit") {
        this.cleanUnusedImagesBeforeUnload(
          cleanUrl,
          this.createRequestPayload(
            pageName,
            event.type,
            uploadDir,
            initialEditorImages
          )
        );
      }
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
      const editorImagesName = editorImagesSrc.map((src) => {
        if (src !== null) return src.split("/").at(-1);
      });

      return editorImagesName;
    } catch (error) {
      throw new Error("Failed to get editor images name: ", error);
    }
  }

  private createRequestPayload(
    pageName: string,
    eventType: string,
    uploadDir: string,
    editorImages: Array<string | null | undefined> = []
  ): string {
    return JSON.stringify({
      pageName: pageName,
      eventType: eventType,
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
        response.error
          ? console.error(response.error)
          : console.log(response.status);
      })
      .catch((error: Error) => {
        console.error("Clean unused images on submit: ", error);
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
    }
  }
}
