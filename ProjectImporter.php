<?php
	requestUrl := "https://redcap.vanderbilt.edu/api/"

	jsonBody := []byte(`format=json&content=record&token=` + token + `&returnFormat=json`)
	resp, err := http.Post(requestUrl, "application/x-www-form-urlencoded", bodyReader)

