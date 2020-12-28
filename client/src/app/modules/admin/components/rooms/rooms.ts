import {Component, OnInit} from '@angular/core';

import {RoomService} from '../../../../services/room';

import {Room} from '../../../../interfaces/room';

@Component({
    selector: 'rooms',
    templateUrl: './rooms.html',
    styleUrls: ['./rooms.less']
})
export class RoomsComponent implements OnInit {

    rooms: Room[] = [];

    constructor(private roomService: RoomService) {}

    ngOnInit() {
        this.roomService.getRooms().subscribe((rooms: [Room]) => {
            this.rooms = rooms;
        }, 'RoomsComponent');
    }

    roomRemove(room: Room) {
        if(!room.room.id) {
            this.rooms = this.rooms.filter((item) => item !== room);
        } else {
            this.roomService.removeRoom(room);
        }
    }

    roomSave(room: Room) {
        this.roomService.saveRoom(room);
    }

    roomAdd() {
        this.rooms.push({
            room: {
                id: null,
                name: null,
            },
            devices: [],
            groups: [],
        });
    }
}