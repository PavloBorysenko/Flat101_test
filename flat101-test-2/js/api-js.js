'use strict';

function flat101FetchPostData(APIUrl) {
  jQuery.ajax({
    url: APIUrl,
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      console.log(response);
    },
    error: function(xhr, status, error) {
      console.log(error); 
    }
  });
}


jQuery(document).ready(function() {
    if ('undefined' != typeof flat101_post_data) {
	console.log(flat101_post_data);
	flat101FetchPostData(flat101_post_data.api_url + flat101_post_data.post_id);
    }
});
