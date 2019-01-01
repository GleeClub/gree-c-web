# Gree-C-Web JSON API

## Overview

Each request has a query parameter called `action` in its URL, which is used to route the request for further processing.
Action-specific arguments may be accepted through query parameters or POST data, as appropriate.  Parameters are expected in the
query string unless specifically mentioned.

The response is a JSON document unless otherwise specified.  Each such JSON response contains a key called `status` indicating the
status of the request.  This field may take any of the following values:

  - `ok`: The request was processed successfully and the appropriate response data for the selected action is included in the
    response.
  - `error`: The client made an invalid request.  An error message appropriate for display to the user is available in the `message`
    key, and other diagnostic information may be present.
  - `internal_error`: The request could not be completed due to a bug in the server.  A basic error message is available in the
    `message` key.  Detailed diagnostic information should not be present in the response.

The standard API endpoint is <https://gleeclub.gatech.edu/buzz/api.php>.

Note that the sample responses below are truncated for brevity.

## Public Actions

The following actions can be requested without any authentication.

### `auth` – authenticate as a user

**Sample Session:**

```
$ curl -s 'https://gleeclub.gatech.edu/buzz/api.php?action=auth' --data '{"user":"gburdell3@gatech.edu","pass":"spring08"}' | jq
{
  "identity": "AcGuO6+kAWp11AQ+SKQWfD/bekyKoXkYkzh/vZNshuQ=",
  "choir": "glee",
  "status": "ok"
}
```

### `publicEvents` – public event information

**Parameters:** none

**Sample Response:**

```json
{
  "events": [
    {
      "id": 2332,
      "name": "Kickoff",
      "time": 1534800300,
      "location": "WV 175",
      "summary": "Come have fun!",
      "description": "Learn about the choirs and decide to join the Glee Club."
    }
  ],
  "status": "ok"
}
```

### `calendar` – iCal invitation for an event

**Parameters:**

  - `event`: ID of the event to retrieve

**Sample Response:**

```
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
BEGIN:VEVENT
UID:20190101T203320Z@gleeclub.gatech.edu
DTSTAMP:20190101T203320Z
DTSTART:20181127T010000Z
DTEND:20181127T023000Z
SUMMARY:Semester Concert
DESCRIPTION:summary
LOCATION:WV 175
END:VEVENT
END:VCALENDAR
```

**Notes:**

  - It's not visible in the sample response, but the iCal format requires lines to be separated by `\r\n`.

### `publicSongs` – public repertoire information

**Parameters:** none

**Sample Response:**

```json
{
  "songs": [
    {
      "id": 16,
      "title": "Anacreontic Song",
      "links": [
        {
          "id": 476,
          "name": "The Anacreontic Song (ft. Andy), Academy of Medicine with Andy Offut Irwin, Fall 2009",
          "ytid": "OqyQO3xhNx0"
        }
      ]
    }
  ],
  "status": "ok"
}
```

### `gigRequest` – make a gig request

In progress.


## Authorized Actions

The following actions require authentication as a registered member, and possibly additional privileges as listed below.
Authentication can be provided by

 1. providing an `X-Identity` HTTP header containing the value at the `identity` key in the document returned by a successful call
    to `auth`, or
 2. providing the `email` cookie set by a regular login through the Gree-C-Web web UI.

### `attendance` – member attendance record

**Privilege:** `view-attendance` if the user whose information is being requested is different from the authenticated user

**Parameters:**

  - `member` (default current user): e-mail of the user whose information is desired

**Sample Response:**

```json
{
  "attendance": [
    {
      "eventNo": 2332,
      "name": "Kickoff",
      "date": 1534798200,
      "type": "volunteer",
      "typeName": "Volunteer Gig",
      "shouldAttend": false,
      "didAttend": false,
      "late": 0,
      "pointChange": 0,
      "partialScore": 100,
      "explanation": "Did not attend and not expected to",
      "gigCount": false
    }
  ],
  "finalScore": 100,
  "gigCount": 4,
  "gigReq": 5,
  "status": "ok"
}
```

### `events` – semester event list for a member

**Privilege:** `view-attendance` if the member being requested is different from the authenticated user

**Parameters:**

  - `member` (default current user): e-mail of the user whose information is desired
  - `semester` (default current semester): name (e.g., "Fall 2017") of the semester to retrieve

**Sample Response:**

```json
{
  "events": [
    {
      "id": 2299,
      "name": "Graduate Student Convocation",
      "call": 1535571900,
      "perform": 1535574600,
      "release": 1535578200,
      "points": 10,
      "comments": "We will be singing the national anthem, alma mater, and ramblin wreck.",
      "type": "volunteer",
      "location": "McHamish Pavilion",
      "section": "None",
      "gigcount": true,
      "uniform": "slacks",
      "contact": "",
      "shouldAttend": false,
      "didAttend": false,
      "confirmed": true,
      "late": 0
    }
  ],
  "status": "ok"
}
```

### `attendees` – see who's attending

**Privilege:** member

**Parameters:**

  - `event`: ID of event to retrieve

**Sample Response:**

```json
{
  "attendees": [
    {
      "memberID": "awesome@gatech.edu",
      "shouldAttend": false,
      "confirmed": true
    }
  ],
  "status": "ok"
}
```

### `members` – member roster

**Privilege:** member

**Parameters:** none

**Sample Response:**

```json
{
  "members": [
    {
      "positions": [
        "Webmaster"
      ],
      "name": "Shower-san Schauer",
      "quote": "Bro, do you even lift?",
      "picture": "https://lumiere-a.akamaihd.net/v1/images/C-3PO-See-Threepio_68fe125c.jpeg?region=0%2C1%2C1408%2C792&",
      "email": "awesome@gatech.edu",
      "phone": 15053109012,
      "location": "The Cloud",
      "car": "No",
      "major": "Computer Science",
      "techYear": 7,
      "section": "Bass",
      "enrollment": "Club",
      "hometown": "Los Alamos, New Mexico",
      "gigs": 4,
      "score": 100,
      "balance": -40,
      "dues": -20
    }
  ],
  "status": "ok"
}
```

**Notes:**

  - The sample response above contains all available member information.  Some information may be hidden based on the permissions of
    the authenticated user.
  - Because retrieving all member information for every user is time-consuming, use the `member` action if information on one or a
    few specific members is required.

### `updateAttendance` – update own attendance

**Privilege:** member

**Parameters:**

  - `event`: ID of event for which to update attendance
  - `attend`: `1` to set attending; `0` to set not attending

**Response:** `status` only

**Notes:**

  - The API for this action may change to allow officers to use it to update other members' attendance.

### `songs` – repertoire list

**Privilege:** member

**Parameters:** none

**Sample Response:**

```json
{
  "songs": [
    {
      "id": 13,
      "choir": "glee",
      "title": "Alma Mater",
      "info": "",
      "current": true,
      "key": "A♭",
      "pitch": "E♭",
      "links": [
        {
          "id": 504,
          "type": "video",
          "name": "Alma Mater at 2013 football game",
          "target": "R5wSQhPi3No"
        },
        {
          "id": 663,
          "type": "pdf",
          "name": "Alma Mater",
          "target": "almaMater.pdf"
        }
      ]
    }
  ],
  "music_dir": "/music",
  "status": "ok"
}
```

### `member` – member profile

**Privilege:** member

**Parameters:**

  - `member`: the e-mail address of the member whose information should be retrieved

**Sample Response:**

```json
{
  "profile": {
    "positions": [
      "Webmaster"
    ],
    "name": "Shower-san Schauer",
    "quote": "Bro, do you even lift?",
    "picture": "https://lumiere-a.akamaihd.net/v1/images/C-3PO-See-Threepio_68fe125c.jpeg?region=0%2C1%2C1408%2C792&",
    "email": "awesome@gatech.edu",
    "phone": 15053109012,
    "location": "The Cloud",
    "car": "No",
    "major": "Computer Science",
    "techYear": 7,
    "section": "Bass",
    "enrollment": "Club",
    "hometown": "Los Alamos, New Mexico",
    "gigs": 4,
    "score": 100,
    "balance": -40,
    "dues": -20
  },
  "status": "ok"
}
```

**Notes:**

  - The sample response above contains all available member information.  Some information may be hidden based on the permissions of
    the authenticated user.

### `carpools` – carpools for an event

**Privilege:** member

**Parameters:**

  - `event`: event ID to retrieve

**Sample Response:**

```json
{
  "carpools": [
    {
      "id": 595,
      "driver": "iop@gatech.edu",
      "passengers": [
        "zxc@gatech.edu",
        "asd@gatech.edu",
        "qwe@gatech.edu"
      ]
    }
  ],
  "status": "ok"
}
```

### `updateCarpools` – update carpools for an event

In progress.

### `setList` – set list for an events

In progress.
