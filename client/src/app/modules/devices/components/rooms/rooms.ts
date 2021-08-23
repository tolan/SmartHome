import {Component, OnInit, OnDestroy} from '@angular/core'

import {DeviceService} from '../../../../services/device'

import {Room} from '../../../../interfaces/room'

@Component({
    selector: 'rooms',
    templateUrl: './rooms.html',
    styleUrls: ['./rooms.less']
})
export class RoomsComponent implements OnInit, OnDestroy {

    public rooms: Room[] = []

    constructor(private deviceService: DeviceService) {
    }

    ngOnInit() {
        this.deviceService.getControlled().subscribe((devices: any[]) => {
            this.rooms = Object.values(devices.reduce((acc: {[key: number]: any}, device: {room: {id: number}}) => {
                const room = {
                    'room': device.room,
                }
                acc[device.room.id] = room
                return acc
            }, {}))
        }, 'DevicesRoomsComponent')
    }

    ngOnDestroy() {
        this.deviceService.getControlled().unsubscribe('DevicesRoomsComponent')
    }
}