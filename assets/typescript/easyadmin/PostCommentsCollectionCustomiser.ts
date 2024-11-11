class PostCommentsCollectionCustomiser {
  public removeAllowDeleteIfUnauthorizedUser(): void {
    const postCommentsCollection: NodeListOf<HTMLDivElement> =
      document.querySelectorAll<HTMLDivElement>("div.field-collection-item");

    postCommentsCollection.forEach((item) => {
      const divWithDataAllowDeleteItem = item.querySelector<HTMLDivElement>(
        "div[data-allow-delete-item]"
      );

      if (!divWithDataAllowDeleteItem) return;

      const deleteButton = item.querySelector<HTMLButtonElement>(
        "button.field-collection-delete-button"
      );

      if (!deleteButton) return;

      if (divWithDataAllowDeleteItem.dataset.allowDeleteItem === "false") {
        deleteButton.remove();
      }
    });
  }
}

document.addEventListener("DOMContentLoaded", () => {
  new PostCommentsCollectionCustomiser().removeAllowDeleteIfUnauthorizedUser();
});
