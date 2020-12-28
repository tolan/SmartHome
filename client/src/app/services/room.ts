import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {ContainerFactory, Container} from '../lib/container';
import {Mediator, buildKeys, EventGroup, EventAction} from '../lib/mediator';

import {Room} from '../interfaces/room';

@Injectable({
    providedIn: 'root'
})
export class RoomService {

    private container: Container;

    constructor(private http: HttpClient, private mediator: Mediator, containerFactory: ContainerFactory) {
        this.container = containerFactory.getContainer('RoomService');
        this._fetchRooms();
        this.mediator.on(buildKeys(EventGroup.DEVICE), () => this._fetchRooms());
        this.mediator.on(buildKeys(EventGroup.GROUP), () => this._fetchRooms());
    }

    getRooms() {
        return this.container.get('rooms');
    }

    saveRoom(room: Room) {
        if (!room.room.id) {
            this.http.put('/api/0/room', {...room}).subscribe(() => {
                this._fetchRooms();
                this.mediator.emit(buildKeys(EventGroup.ROOM, EventAction.create), room);
            });
        } else {
            this.http.post('/api/0/room', {...room}).subscribe(() => {
                this._fetchRooms();
                this.mediator.emit(buildKeys(EventGroup.ROOM, EventAction.update), room);
            });
        }
    }

    removeRoom(room: Room) {
        this.http.delete('/api/0/room/' + room.room.id).subscribe(() => {
            this._fetchRooms();
            this.mediator.emit(buildKeys(EventGroup.ROOM, EventAction.remove), room);
        });
    }

    private _fetchRooms() {
        this.http.get('/api/0/rooms').subscribe((rooms: [Room]) => {
            this.container.set('rooms', rooms);
        });
    }
}
