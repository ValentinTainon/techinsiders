export default class EditorMediaCleaner {
  private pageName: string | undefined;
  private postUuid: string | undefined;
  private eventType: string;
  private editorInput: HTMLDivElement | null;
  private imgPathsOnLoad: (string | null)[];

  constructor(editorDataset: DOMStringMap) {
    this.pageName = editorDataset.pageName;
    this.postUuid = editorDataset.postUuid;
    this.editorInput = document.querySelector<HTMLDivElement>(
      "div.ck-editor__editable"
    );
  }

  public cleanUnusedImages(): void {
    if (!this.pageName || !this.postUuid || !this.editorInput) {
      throw new Error(
        "Cannot initialize EditorImageCleaner due to missing page name or post UUID or editor input."
      );
    }

    let isFormSubmitted = false;

    if (this.pageName === "edit") {
      this.imgPathsOnLoad = this.getAllImgPaths();
    }

    document.addEventListener("ea.form.submit", (event: Event) => {
      const customEvent = event as CustomEvent;
      this.eventType = customEvent.type;

      this.cleanUnusedImagesOnSubmit(
        this.createRequestPayload(this.getAllImgPaths())
      );

      isFormSubmitted = true;
    });

    window.addEventListener("beforeunload", (event: BeforeUnloadEvent) => {
      if (isFormSubmitted) return;

      this.eventType = event.type;

      if (this.pageName === "new") {
        this.cleanUnusedImagesBeforeUnload(this.createRequestPayload());
      }

      if (this.pageName === "edit") {
        this.cleanUnusedImagesBeforeUnload(
          this.createRequestPayload(this.imgPathsOnLoad)
        );
      }
    });
  }
  private getAllImgPaths(): (string | null)[] {
    return Array.from(
      this.editorInput?.querySelectorAll<HTMLImageElement>("img") ?? []
    ).map((image) => `public${image.getAttribute("src")}`);
  }

  private createRequestPayload(postImgPaths: (string | null)[] = []): string {
    return JSON.stringify({
      pageName: this.pageName,
      eventType: this.eventType,
      postUuid: this.postUuid,
      postImgPaths: postImgPaths,
    });
  }

  private async cleanUnusedImagesOnSubmit(payload: string): Promise<void> {
    try {
      const response = await fetch("/handle-deleted-post-images", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: payload,
      });

      const data = await response.json();

      data.error ? console.error(data.error) : console.log(data.status);
    } catch (error) {
      console.error("Clean unused images on submit: ", error);
    }
  }

  private cleanUnusedImagesBeforeUnload(payload: string): void {
    try {
      const data = new Blob([payload], {
        type: "application/json",
      });

      const sendBeaconAction = navigator.sendBeacon(
        "/handle-deleted-post-images",
        data
      );

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
