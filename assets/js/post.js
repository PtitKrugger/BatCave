function toggleLikePost(postId) {
    const likeSvg = document.querySelector(`#like-${postId} .likeSvg`);
    const likeCount = document.querySelector(`#like-${postId} .likeCount`);
    const isLiked = likeSvg.getAttribute('data-liked') === 'true';

    const method = isLiked ? 'DELETE' : 'POST';
    const url = isLiked ? `/post/${postId}/unlike` : `/post/${postId}/like`;

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => {
        if (response.status === 204) {
            likeSvg.classList.toggle('text-blue-500', !isLiked);
            likeSvg.classList.toggle('text-white', isLiked);
            likeSvg.classList.toggle('hover:text-blue-500', isLiked);

            likeCount.textContent = parseInt(likeCount.textContent) + (isLiked ? -1 : 1);

            likeSvg.setAttribute('data-liked', !isLiked);
        } 
        else if (response.status === 429) {
            throw new Error('Veuillez patienter avant une nouvelle action');
        }
        else {
            throw new Error('Une erreur est survenue lors de la requête');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}
