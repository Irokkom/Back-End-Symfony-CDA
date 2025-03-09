document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour ajouter un nouveau média
    function addMediaForm(collectionHolder, prototype) {
        // Crée un nouvel élément li
        const newForm = document.createElement('div');
        newForm.classList.add('media-item', 'mb-3', 'p-3', 'border', 'rounded');

        // Récupère le prototype avec l'index correct
        const index = collectionHolder.dataset.index;
        const newFormHtml = prototype.replace(/__name__/g, index);
        newForm.innerHTML = newFormHtml;

        // Ajoute le bouton de suppression
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.classList.add('btn', 'btn-danger', 'mt-2');
        removeButton.innerHTML = 'Supprimer ce média';
        removeButton.addEventListener('click', function() {
            newForm.remove();
        });
        newForm.appendChild(removeButton);

        // Ajoute le nouveau formulaire à la collection
        collectionHolder.appendChild(newForm);

        // Incrémente l'index
        collectionHolder.dataset.index = parseInt(index) + 1;

        // Ajoute les écouteurs pour le changement de type
        setupTypeListener(newForm);
    }

    // Fonction pour gérer l'affichage des champs en fonction du type
    function setupTypeListener(formElement) {
        const typeSelect = formElement.querySelector('select[id$="_type"]');
        const fileDiv = formElement.querySelector('[id$="_file"]')?.closest('.form-group');
        const urlDiv = formElement.querySelector('[id$="_url"]')?.closest('.form-group');

        if (typeSelect && (fileDiv || urlDiv)) {
            typeSelect.addEventListener('change', function() {
                if (this.value === 'link') {
                    if (fileDiv) fileDiv.style.display = 'none';
                    if (urlDiv) urlDiv.style.display = 'block';
                } else {
                    if (fileDiv) fileDiv.style.display = 'block';
                    if (urlDiv) urlDiv.style.display = 'none';
                }
            });

            // Déclencher l'événement au chargement
            typeSelect.dispatchEvent(new Event('change'));
        }
    }

    // Initialisation
    const collectionHolder = document.querySelector('.media-collection');
    if (collectionHolder) {
        // Initialise l'index
        collectionHolder.dataset.index = collectionHolder.querySelectorAll('.media-item').length;

        // Ajoute le bouton "Ajouter un média"
        const addButton = document.createElement('button');
        addButton.type = 'button';
        addButton.classList.add('btn', 'btn-primary', 'mb-3');
        addButton.innerHTML = 'Ajouter un média';
        
        addButton.addEventListener('click', function() {
            addMediaForm(collectionHolder, collectionHolder.dataset.prototype);
        });

        collectionHolder.parentElement.insertBefore(addButton, collectionHolder);

        // Configure les écouteurs pour les formulaires existants
        document.querySelectorAll('.media-item').forEach(setupTypeListener);
    }
});
