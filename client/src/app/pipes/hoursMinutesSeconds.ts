import {Pipe, PipeTransform} from '@angular/core';

@Pipe({name: 'hoursMinutesSeconds'})
export class HoursMinutesSeconds implements PipeTransform {
    transform(input: number): string {
        const hours = Math.floor(input / 3600);
        const minutes = Math.floor((input % 3600) / 60);
        const seconds = Math.floor(input) % 60;
        let result = `${minutes
            .toString()
            .padStart(1, '0')}:${seconds.toString().padStart(2, '0')}`;
        if (!!hours) {
            result = `${hours.toString()}:${minutes
                .toString()
                .padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        return result;
    }
}
